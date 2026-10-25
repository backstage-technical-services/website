<?php

namespace Database\Factories\Resources;

use App\Models\Resources\Issue;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * An issue (version) of a resource. The file is stored as resources/resources/{resourceId}/issNN.pdf; use withFile()
 * to write one.
 *
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /**
     * The contents of a tiny PDF.
     */
    public const PDF = "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";

    protected $model = Issue::class;

    public function definition(): array
    {
        return [
            'issue' => 1,
            'resource_id' => ResourceFactory::new(),
            'author_id' => UserFactory::new(),
            'reason' => 'Initial issue',
        ];
    }

    public function number(int $issue): static
    {
        return $this->state(['issue' => $issue]);
    }

    public function withFile(string $contents = self::PDF): static
    {
        return $this->afterCreating(function (Issue $issue) use ($contents) {
            File::ensureDirectoryExists(dirname($issue->getPath()));
            File::put($issue->getPath(), $contents);
        });
    }
}
