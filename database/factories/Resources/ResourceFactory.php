<?php

namespace Database\Factories\Resources;

use App\Models\Resources\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A document in the resources library. Most pages need at least one issue (version) with a file, so use withIssue().
 * Tags are attached with hasAttached(TagFactory::new(), [], 'tags'), as resource_tag is a join table.
 *
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'category_id' => null,
            'event_id' => null,
            'type' => Resource::TYPE_FILE,
            'href' => null,
            'access' => Resource::ACCESS_PUBLIC,
        ];
    }

    /**
     * Set who can see the resource: Resource::ACCESS_PUBLIC, ACCESS_REGISTERED, ACCESS_MEMBER or ACCESS_COMMITTEE.
     */
    public function access(?int $access): static
    {
        return $this->state(['access' => $access]);
    }

    /**
     * Add an issue (version) of the file, and write its PDF.
     */
    public function withIssue(int $issue = 1, string $contents = IssueFactory::PDF): static
    {
        return $this->has(IssueFactory::new()->number($issue)->withFile($contents), 'issues');
    }
}
