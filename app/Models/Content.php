<?php
declare(strict_types=1);

namespace App\Models;

final class Content
{
    private function samples(): array
    {
        static $items;
        return $items ??= require dirname(__DIR__) . '/sample.php';
    }

    public function published(string $type): array
    {
        if (!database_ready()) {
            return array_values(array_filter($this->samples(), fn (array $item): bool => $item['type'] === $type));
        }

        $statement = db()->prepare('SELECT id,type,slug,title,published_data AS data,is_published,sort_order FROM content_documents WHERE type=? AND is_published=1 ORDER BY sort_order,id');
        $statement->execute([$type]);
        return array_map(fn (array $row): array => [...$row, 'data' => json_decode($row['data'], true)], $statement->fetchAll());
    }

    public function publishedOne(string $type, ?string $slug = null): ?array
    {
        foreach ($this->published($type) as $item) {
            if ($slug === null || $item['slug'] === $slug) return $item;
        }
        return null;
    }

    public function adminContent(): array
    {
        if (!database_ready()) return $this->samples();
        $rows = db()->query('SELECT id,type,slug,title,draft_data AS data,is_published,sort_order FROM content_documents ORDER BY type,sort_order,id')->fetchAll();
        return array_map(fn (array $row): array => [...$row, 'data' => json_decode($row['data'], true)], $rows);
    }

    public function layoutSettings(): array
    {
        $default = ['resume_template'=>'classic','reflection_template'=>'academic','font_family'=>'Times New Roman','font_size'=>12,'line_height'=>1.5,'section_spacing'=>16];
        if (!database_ready()) return $default;
        return db()->query('SELECT * FROM document_layouts WHERE id=1')->fetch() ?: $default;
    }
}
