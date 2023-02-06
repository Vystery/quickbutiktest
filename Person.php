<?php

namespace App\Traits;

trait Person
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function setFirstNameAttribute($value): void {
        $this->attributes['first_name'] = trim(Formatter::nameCase($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = trim(new Formatter::nameCase($value));
    }

    public function display_name(bool $withFlag = false, bool $show_team_members = true, bool $sixteenLetterCheck = false): string
    {
        $name = Encoding::toUTF8($this->first_name.' '.$this->last_name);
        $withFlag = (is_bool($withFlag)) ? true : false;
        $withFlag = ($withFlag && $this->country_id) ? $this->country->flag() : '';

        if ($this->team_members && $showTeamMembers) {
            $name .= ' (';

            $players = Player::whereIn('id', $this->team_members)->get();

            $firstNames = $players->pluck('first_name')->all();
            $lastNames = $players->pluck('last_name')->all();
            $firstLetters = array_map(function ($firstName) {
                return substr($firstName, 0, 1);
            }, $firstNames);
            $separator = '';

            foreach ($players as $player) {
                $name .= (array_count_values($firstLetters)[substr($player->first_name, 0, 1)] > 1 && array_count_values($lastNames)[$player->last_name] > 1) ? $separator . $player->first_name . ' ' . $player->last_name : $separator . substr($player->first_name, 0, 1) . '. ' . $player->last_name;
                $separator = ', ';
            }

            $name .= ')';
        } else {
            if ($sixteenLetterCheck) {
            if (strlen($this->first_name.' '.$this->last_name) > 16) {
                $name = substr($this->first_name, 0, 1) . '. ' . $this->last_name;
            }
        }}

        return [
            'flag' => $withFlag,
            'spacer' => ' ',
            'name' => $name,
        ];
    }
}