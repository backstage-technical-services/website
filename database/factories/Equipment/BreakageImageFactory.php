<?php

namespace Database\Factories\Equipment;

use App\Models\Equipment\BreakageImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * A photo of a breakage. The row doesn't come with a file: use withFile() to write one, which is stored as
 * resources/breakages/{id}.{extension}.
 *
 * @extends Factory<BreakageImage>
 */
class BreakageImageFactory extends Factory
{
    protected $model = BreakageImage::class;

    public function definition(): array
    {
        return [
            'equipment_breakage_id' => BreakageFactory::new(),
            'mime' => 'image/jpeg',
            'extension' => 'jpg',
        ];
    }

    public function withFile(string $contents = 'not really a jpeg'): static
    {
        return $this->afterCreating(function (BreakageImage $image) use ($contents) {
            File::ensureDirectoryExists(dirname($image->getImagePath()));
            File::put($image->getImagePath(), $contents);
        });
    }
}
