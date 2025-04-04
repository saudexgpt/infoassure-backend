<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Support\Facades\Http;



class ReCaptcha implements Rule
{

    /**

     * Create a new rule instance.

     *

     * @return void

     */

    public function __construct()
    {
    }

    /**

     * Determine if the validation rule passes.

     *

     * @param  string  $attribute

     * @param  mixed  $value

     * @return bool

     */

    public function passes($attribute, $value)
    {
        $response = Http::asForm()->post("https://www.google.com/recaptcha/api/siteverify", [

            'secret' => '6Leh8wkrAAAAABoldut5NjRkc8H_VFaRFhwjkAv_', // env('RECAPTCHA_SITE_SECRET'), // config('services.recaptcha.secret'),

            'response' => $value,

            'remoteip' => request()->ip()

        ]);



        return $response->json()["success"];
    }

    /**

     * Get the validation error message.

     *

     * @return string

     */

    public function message()
    {

        return 'The google recaptcha is required.';
    }
}
