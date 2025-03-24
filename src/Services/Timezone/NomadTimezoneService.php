<?php

namespace TechiesAfrica\Nomad\Services\Timezone;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class NomadTimezoneService
{
    protected $timezone;
    protected $model;

    public function __construct($user_id = null)
    {
        $table = Config::get('nomad.table', "users");
        $user_id ??= auth(Config::get("nomad.guard"))->id();
        $this->model = DB::table($table)->where("id", $user_id);
    }

    /**
     * Set timezone
     * 
     * @param string $timezone  Current timezone of the user
     * @return $this
     */
    public function setTimezone(string $timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Set user ID manually
     * 
     * @param int $userId
     * @return $this
     */
    public function setUser(int $user_id)
    {
        $this->model = DB::table(Config::get('nomad.table', "users"))->where("id", $user_id);
        return $this;
    }

    /**
     * Validate timezone before saving
     *  @return array
     */
    private function validate(): array
    {
        $data = ['timezone' => $this->timezone];
        return Validator::make($data, [
            "timezone" => "required|string",
        ])->validate();
    }

    public function save()
    {
        $data = $this->validate();
        return $this->model?->update([
            "timezone" => $data["timezone"]
        ]);
    }
}
