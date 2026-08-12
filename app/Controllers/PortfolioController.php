<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Content;

final class PortfolioController
{
    public function __construct(private readonly Content $content) {}

    public function handle(): void
    {
        start_secure_session();
        $page = $_GET['page'] ?? 'home';
        $slug = $_GET['slug'] ?? null;
        $error = null;

        if ($page === 'heartbeat') $this->heartbeat();

        if (in_array($page, ['admin', 'edit', 'layouts'], true) && !owner_logged_in()) $this->redirect('login');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $error = $this->handlePost();
        if ($page === 'reflections') {
            $reflection = $this->content->publishedOne('reflection');
            if ($reflection) {
                header('Location: /?page=reflection&slug=' . urlencode($reflection['slug']));
                exit;
            }
        }

        View::render('portfolio', [
            'page' => $page,
            'slug' => $slug,
            'error' => $error,
            'profile' => $this->content->publishedOne('profile')['data'] ?? [],
            'contact' => $this->content->publishedOne('contact')['data'] ?? [],
            ...$this->pageData($page, $slug),
        ]);
    }

    private function pageData(string $page, ?string $slug): array
    {
        return match ($page) {
            'home' => [],
            'about' => ['system' => $this->content->publishedOne('current_system')['data'] ?? []],
            'activities' => $this->listingData($page),
            'activity', 'reflection' => ['item' => $this->content->publishedOne($page, $slug), 'layout' => $this->content->layoutSettings()],
            'resume' => ['item' => $this->content->publishedOne('resume'), 'layout' => $this->content->layoutSettings()],
            'admin' => ['items' => array_values(array_filter(
                $this->content->adminContent(),
                fn (array $item): bool => !in_array($item['type'], ['current_system', 'project'], true)
            ))],
            'edit' => ['item' => $this->findAdminItem((string) ($_GET['id'] ?? ''))],
            'layouts' => ['layout' => $this->content->layoutSettings()],
            default => [],
        };
    }

    private function listingData(string $page): array
    {
        $type = ['activities'=>'activity','reflections'=>'reflection'][$page];
        return ['type' => $type, 'items' => $this->content->published($type)];
    }

    private function findAdminItem(string $id): ?array
    {
        foreach ($this->content->adminContent() as $item) {
            if ((string) $item['id'] === $id) return $item;
        }
        return null;
    }

    private function handlePost(): ?string
    {
        $action = $_POST['action'] ?? '';
        if ($action === 'login') {
            verify_csrf();
            if (!database_ready()) return 'Set up and seed MySQL before signing in.';
            $statement = db()->prepare('SELECT * FROM owners WHERE email=? LIMIT 1');
            $statement->execute([strtolower(trim($_POST['email'] ?? ''))]);
            $owner = $statement->fetch();
            if ($owner && password_verify($_POST['password'] ?? '', $owner['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['role'] = 'owner';
                $_SESSION['owner_id'] = $owner['id'];
                $this->redirect('admin');
            }
            return 'Invalid email or password.';
        }
        if ($action === 'logout') {
            verify_csrf();
            session_destroy();
            $this->redirect('home');
        }
        if (!in_array($action, ['save','publish','unpublish','delete','create','layout','upload_pdf','upload_word'], true)) return null;

        require_owner();
        verify_csrf();
        if ($action === 'upload_pdf') return $this->uploadPdf();
        if ($action === 'upload_word') {
            try {
                return $this->uploadWord();
            } catch (\Throwable $exception) {
                error_log('Word import failed: ' . $exception->getMessage());
                return 'The Word document could not be imported on this server. Please try a PDF if the problem continues.';
            }
        }
        if ($action === 'layout') {
            $statement = db()->prepare('UPDATE document_layouts SET resume_template=?,reflection_template=?,font_family=?,font_size=?,line_height=?,section_spacing=? WHERE id=1');
            $statement->execute([$_POST['resume_template'],$_POST['reflection_template'],$_POST['font_family'],(float)$_POST['font_size'],(float)$_POST['line_height'],(int)$_POST['section_spacing']]);
            $this->redirect('layouts');
        }
        if ($action === 'create') {
            $type = $_POST['type'] ?? '';
            if (!in_array($type, ['project','activity','reflection'], true)) exit('Invalid content type.');
            $slug = 'new-' . $type . '-' . time();
            $data = ['title' => 'New ' . ucfirst($type), 'slug' => $slug, 'summary' => 'Add sample content here.'];
            $statement = db()->prepare('INSERT INTO content_documents(type,slug,title,draft_data,sort_order) VALUES(?,?,?,?,0)');
            $statement->execute([$type, $slug, $data['title'], json_encode($data)]);
            header('Location: /?page=edit&id=' . db()->lastInsertId());
            exit;
        }

        $id = $_POST['id'] ?? '';
        if ($action === 'save' || $action === 'publish') {
            if (isset($_POST['profile_form'])) {
                $skills = preg_split('/\s*[,\n]\s*/', trim($_POST['skills'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'full_name' => trim($_POST['full_name'] ?? ''),
                    'role' => trim($_POST['role'] ?? ''),
                    'location' => trim($_POST['location'] ?? ''),
                    'availability' => trim($_POST['availability'] ?? ''),
                    'age' => trim($_POST['age'] ?? ''),
                    'birthdate' => trim($_POST['birthdate'] ?? ''),
                    'school' => trim($_POST['school'] ?? ''),
                    'course_year' => trim($_POST['course_year'] ?? ''),
                    'dream_job' => trim($_POST['dream_job'] ?? ''),
                    'intro' => trim($_POST['intro'] ?? ''),
                    'biography' => trim($_POST['biography'] ?? ''),
                    'skills' => array_values(array_unique($skills)),
                ];
            } elseif (isset($_POST['resume_form'])) {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'headline' => trim($_POST['headline'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'location' => trim($_POST['location'] ?? ''),
                    'website' => trim($_POST['website'] ?? ''),
                    'summary' => trim($_POST['summary'] ?? ''),
                    'experience' => $this->documentEntries('experience', 'role', 'organization'),
                    'education' => $this->documentEntries('education', 'degree', 'school'),
                    'skills' => $this->skillGroups(),
                ];
                $this->preserveUploadedPdf($id, $data);
            } elseif (isset($_POST['reflection_form'])) {
                $data = [];
                foreach (['title','date','course','instructor','activity','experience','observations','learning','next_steps','conclusion'] as $field) {
                    $data[$field] = trim($_POST[$field] ?? '');
                }
                $this->preserveUploadedPdf($id, $data);
            } else {
                $data = json_decode($_POST['data'] ?? '', true);
            }
            if (!is_array($data)) return 'Content data must be valid JSON.';
            $statement = db()->prepare('UPDATE content_documents SET title=?,slug=?,sort_order=?,draft_data=? WHERE id=?');
            $statement->execute([trim($_POST['title']),trim($_POST['slug']),(int)$_POST['sort_order'],json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),$id]);
            if ($action === 'publish') db()->prepare('UPDATE content_documents SET published_data=draft_data,is_published=1,published_at=NOW() WHERE id=?')->execute([$id]);
            $this->redirect('admin');
        }
        if ($action === 'unpublish') db()->prepare('UPDATE content_documents SET is_published=0 WHERE id=?')->execute([$id]);
        if ($action === 'delete') db()->prepare("DELETE FROM content_documents WHERE id=? AND type IN ('project','activity','reflection')")->execute([$id]);
        $this->redirect('admin');
    }

    private function documentEntries(string $prefix, string $primary, string $secondary): array
    {
        $entries = [];
        foreach (($_POST[$prefix . '_' . $primary] ?? []) as $index => $value) {
            $details = preg_split('/\r?\n/', trim($_POST[$prefix . '_details'][$index] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $entries[] = [
                $primary => trim($value),
                $secondary => trim($_POST[$prefix . '_' . $secondary][$index] ?? ''),
                'period' => trim($_POST[$prefix . '_period'][$index] ?? ''),
                'details' => $prefix === 'education' && count($details) <= 1 ? ($details[0] ?? '') : array_values($details),
            ];
        }
        return $entries;
    }

    private function skillGroups(): array
    {
        $groups = [];
        foreach (($_POST['skill_group'] ?? []) as $index => $group) {
            $items = preg_split('/\s*[,\n]\s*/', trim($_POST['skill_items'][$index] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $groups[] = ['group' => trim($group), 'items' => array_values($items)];
        }
        return $groups;
    }

    private function preserveUploadedPdf(string $id, array &$data): void
    {
        $statement = db()->prepare('SELECT draft_data FROM content_documents WHERE id=? LIMIT 1');
        $statement->execute([$id]);
        $existing = json_decode((string) ($statement->fetchColumn() ?: ''), true) ?: [];
        foreach (['uploaded_pdf','uploaded_pdf_name','uploaded_word','uploaded_word_name'] as $key) if (!empty($existing[$key])) $data[$key] = $existing[$key];
    }

    private function uploadPdf(): ?string
    {
        $id = (string) ($_POST['id'] ?? '');
        $file = $_FILES['pdf'] ?? null;
        if (!$file) return 'Choose a PDF file to import.';
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) return 'The PDF exceeds the server upload limit. Restart the server with composer start and try again.';
        if ($uploadError === UPLOAD_ERR_PARTIAL) return 'The PDF upload was interrupted. Please try again.';
        if ($uploadError === UPLOAD_ERR_NO_FILE) return 'Choose a PDF file to import.';
        if ($uploadError !== UPLOAD_ERR_OK) return 'The PDF could not be uploaded. Please try again.';
        if (($file['size'] ?? 0) > 15 * 1024 * 1024) return 'The PDF must be 15 MB or smaller.';

        $header = file_get_contents($file['tmp_name'], false, null, 0, 5);
        $mime = class_exists(\finfo::class) ? (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : 'application/pdf';
        if ($header !== '%PDF-' || $mime !== 'application/pdf') return 'The selected file is not a valid PDF.';

        $statement = db()->prepare("SELECT id,type,draft_data,published_data,is_published FROM content_documents WHERE id=? AND type IN ('resume','reflection') LIMIT 1");
        $statement->execute([$id]);
        $item = $statement->fetch();
        if (!$item) return 'Only Resume and Reflection entries accept PDF imports.';

        $directory = dirname(__DIR__, 2) . '/public/uploads/documents';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) return 'Unable to create the PDF upload directory.';
        $filename = $item['type'] . '-' . $item['id'] . '-' . bin2hex(random_bytes(6)) . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) return 'Unable to store the uploaded PDF.';

        $path = '/uploads/documents/' . $filename;
        $originalName = basename((string) ($file['name'] ?? 'document.pdf'));
        $draft = json_decode($item['draft_data'], true) ?: [];
        $draft['uploaded_pdf'] = $path;
        $draft['uploaded_pdf_name'] = $originalName;
        $published = $item['published_data'] ? (json_decode($item['published_data'], true) ?: []) : null;
        if ($item['is_published']) {
            $published ??= $draft;
            $published['uploaded_pdf'] = $path;
            $published['uploaded_pdf_name'] = $originalName;
        }
        $update = db()->prepare('UPDATE content_documents SET draft_data=?,published_data=? WHERE id=?');
        $update->execute([
            json_encode($draft, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $published === null ? null : json_encode($published, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $id,
        ]);
        $this->redirect('admin');
    }

    private function uploadWord(): ?string
    {
        $id = (string) ($_POST['id'] ?? '');
        $file = $_FILES['word'] ?? null;
        if (!$file) return 'Choose a Word (.docx) file to import.';
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) return 'The Word file exceeds the server upload limit. Restart the server with composer start and try again.';
        if ($uploadError === UPLOAD_ERR_PARTIAL) return 'The Word upload was interrupted. Please try again.';
        if ($uploadError === UPLOAD_ERR_NO_FILE) return 'Choose a Word (.docx) file to import.';
        if ($uploadError !== UPLOAD_ERR_OK) return 'The Word file could not be uploaded. Please try again.';
        if (($file['size'] ?? 0) > 15 * 1024 * 1024) return 'The Word file must be 15 MB or smaller.';
        if (strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION)) !== 'docx') return 'Only modern Word (.docx) files are supported.';

        if (class_exists(\ZipArchive::class)) {
            $archive = new \ZipArchive();
            if ($archive->open($file['tmp_name']) !== true || $archive->locateName('[Content_Types].xml') === false || $archive->locateName('word/document.xml') === false) {
                if ($archive->status === \ZipArchive::ER_OK) $archive->close();
                return 'The selected file is not a valid Word document.';
            }
            $archive->close();
        } else {
            $header = file_get_contents($file['tmp_name'], false, null, 0, 4);
            if ($header !== "PK\x03\x04") return 'The selected file is not a valid Word document.';
        }

        $statement = db()->prepare("SELECT id,type,draft_data,published_data,is_published FROM content_documents WHERE id=? AND type IN ('resume','reflection') LIMIT 1");
        $statement->execute([$id]);
        $item = $statement->fetch();
        if (!$item) return 'Only Resume and Reflection entries accept Word imports.';

        $directory = dirname(__DIR__, 2) . '/public/uploads/documents';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) return 'Unable to create the document upload directory.';
        $base = 'word-' . $item['type'] . '-' . $item['id'] . '-' . bin2hex(random_bytes(6));
        $wordFilename = $base . '.docx';
        $wordPath = $directory . '/' . $wordFilename;
        if (!move_uploaded_file($file['tmp_name'], $wordPath)) return 'Unable to store the imported Word document.';

        $pdfFilename = $base . '.pdf';
        $originalName = basename((string) ($file['name'] ?? 'document.docx'));
        $dataUpdates = [
            'uploaded_word' => '/uploads/documents/' . $wordFilename,
            'uploaded_word_name' => $originalName,
        ];

        // Shared hosts commonly disable process execution. Conversion is optional:
        // retain the DOCX and only attach a PDF when LibreOffice is actually available.
        if (function_exists('exec') && is_executable('/usr/bin/libreoffice')) {
            $profileDirectory = sys_get_temp_dir() . '/portfolio-libreoffice-' . bin2hex(random_bytes(6));
            if (mkdir($profileDirectory, 0700, true) || is_dir($profileDirectory)) {
                $command = '/usr/bin/libreoffice -env:UserInstallation=' . escapeshellarg('file://' . $profileDirectory)
                    . ' --headless --convert-to pdf --outdir ' . escapeshellarg($directory) . ' ' . escapeshellarg($wordPath) . ' 2>&1';
                $conversionOutput = [];
                $conversionCode = 1;
                exec($command, $conversionOutput, $conversionCode);
                if ($conversionCode === 0 && is_file($directory . '/' . $pdfFilename)) {
                    $dataUpdates['uploaded_pdf'] = '/uploads/documents/' . $pdfFilename;
                    $dataUpdates['uploaded_pdf_name'] = pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                }
            }
        }

        $draft = json_decode($item['draft_data'], true) ?: [];
        $published = $item['published_data'] ? (json_decode($item['published_data'], true) ?: []) : null;
        if (!isset($dataUpdates['uploaded_pdf'])) {
            unset($draft['uploaded_pdf'], $draft['uploaded_pdf_name']);
            if ($published !== null) unset($published['uploaded_pdf'], $published['uploaded_pdf_name']);
        }
        foreach ($dataUpdates as $key => $value) $draft[$key] = $value;
        if ($item['is_published']) {
            $published ??= $draft;
            foreach ($dataUpdates as $key => $value) $published[$key] = $value;
        }
        $update = db()->prepare('UPDATE content_documents SET draft_data=?,published_data=? WHERE id=?');
        $update->execute([
            json_encode($draft, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $published === null ? null : json_encode($published, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $id,
        ]);
        $this->redirect('admin');
    }

    private function redirect(string $page): never
    {
        header('Location: /?page=' . urlencode($page));
        exit;
    }

    private function heartbeat(): never
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        try {
            db()->exec('CREATE TABLE IF NOT EXISTS visitor_activity (visitor_id CHAR(64) PRIMARY KEY,last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX visitor_last_seen(last_seen)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $visitorId = hash('sha256', session_id());
            $statement = db()->prepare('INSERT INTO visitor_activity(visitor_id,last_seen) VALUES(?,NOW()) ON DUPLICATE KEY UPDATE last_seen=NOW()');
            $statement->execute([$visitorId]);
            if (random_int(1, 30) === 1) db()->exec('DELETE FROM visitor_activity WHERE last_seen < NOW() - INTERVAL 1 DAY');
            $count = (int) db()->query('SELECT COUNT(*) FROM visitor_activity WHERE last_seen >= NOW() - INTERVAL 2 MINUTE')->fetchColumn();
            echo json_encode(['active' => $count]);
        } catch (\Throwable) {
            http_response_code(503);
            echo json_encode(['active' => null]);
        }
        exit;
    }
}
