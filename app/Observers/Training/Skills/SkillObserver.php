<?php

namespace App\Observers\Training\Skills;

use App\Logger;
use App\Models\Training\Skills\Skill;
use App\Observers\ModelObserver;

class SkillObserver extends ModelObserver
{
    public function created(Skill $skill)
    {
        Logger::log('training-skill.create', true, $this->getCreatedAttributes($skill));
    }

    public function updated(Skill $skill)
    {
        Logger::log('training-skill.edit', true, $this->getUpdatedAttributes($skill));
    }

    public function deleted(Skill $skill)
    {
        Logger::log('training-skill.delete', true, $this->getDeletedAttributes($skill));
    }
}