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

        if (in_array($page, ['admin', 'edit', 'layouts'], true) && !owner_logged_in()) $this->redirect('login');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $error = $this->handlePost();

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
            'home' => ['system' => $this->content->publishedOne('current_system')['data'] ?? [], 'projects' => $this->content->published('project')],
            'about' => ['system' => $this->content->publishedOne('current_system')['data'] ?? []],
            'projects', 'activities', 'reflections' => $this->listingData($page),
            'project', 'activity', 'reflection' => ['item' => $this->content->publishedOne($page, $slug), 'layout' => $this->content->layoutSettings()],
            'resume' => ['item' => $this->content->publishedOne('resume'), 'layout' => $this->content->layoutSettings()],
            'admin' => ['items' => $this->content->adminContent()],
            'edit' => ['item' => $this->findAdminItem((string) ($_GET['id'] ?? ''))],
            'layouts' => ['layout' => $this->content->layoutSettings()],
            default => [],
        };
    }

    private function listingData(string $page): array
    {
        $type = ['projects'=>'project','activities'=>'activity','reflections'=>'reflection'][$page];
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
        if (!in_array($action, ['save','publish','unpublish','delete','create','layout'], true)) return null;

        require_owner();
        verify_csrf();
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
            $data = json_decode($_POST['data'] ?? '', true);
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

    private function redirect(string $page): never
    {
        header('Location: /?page=' . urlencode($page));
        exit;
    }
}
