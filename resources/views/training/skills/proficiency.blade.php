<span class="skill-proficiency" title="{{ $level > 0 ? $LevelNames[$level] : 'None' }}">
    @for($i = 1; $i <= $level; $i++)
        <span class="fa fa-star"></span>
    @endfor
    @for(; $i<=3; $i++)
        <span class="fa fa-star-o"></span>
    @endfor
</span>