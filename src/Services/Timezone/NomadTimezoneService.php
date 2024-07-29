<?php

namespace TechiesAfrica\Nomad\Services\Timezone;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class NomadTimezoneService
{
    protected $user;
    protected $timezone;
    protected $model;

    public function __construct()
    {
        $table = Config::get('nomad.table', "users");
        $this->model = DB::table($table);
    }

    /**
     * Set timezone
     *  @param $timezone  Current timezone of the user;
     *  @return $this
     */
    public function setTimezone(string $timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Validate timezone before saving
     *  @return array
     */
    private function validate(): array
    {
        $data = (array) $this;
        $validator = Validator::make($data, [
            "timezone" => "required|string",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save()
    {
        $data = $this->validate();
        return $this->model->update([
            "timezone" => $data["timezone"]
        ]);
    }
}
