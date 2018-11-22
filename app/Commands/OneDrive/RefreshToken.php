<?php

namespace App\Commands\OneDrive;

use App\Helpers\Constants;
use App\Helpers\Tool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class RefreshToken extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'refresh:token';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Refresh Token';

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_secret;

    /**
     * @var string
     */
    protected $redirect_uri;

    /**
     * @var string
     */
    protected $authorize_url;

    /**
     * @var string
     */
    protected $access_token_url;

    /**
     * @var string
     */
    protected $scopes;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client_id = Tool::config('client_id');
        $this->client_secret = Tool::config('client_secret');
        $this->redirect_uri = Tool::config('redirect_uri');
        $this->authorize_url = Tool::config('app_type') == 'com' ? Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT : Constants::AUTHORITY_URL_21V . Constants::AUTHORIZE_ENDPOINT_21V;
        $this->access_token_url = Tool::config('app_type') == 'com' ? Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT : Constants::AUTHORITY_URL_21V . Constants::TOKEN_ENDPOINT_21V;
        $this->scopes = Constants::SCOPES;
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if (!Tool::hasBind() || !Tool::hasConfig()) {
            $this->error('未绑定账户');
            exit;
        }
        $expires = Tool::config('access_token_expires', 0);
        $hasExpired = $expires - time() <= 0;
        if (!$hasExpired) {
            return;
        }
        $existingRefreshToken = Tool::config('refresh_token');
        try {
            $client = new Client();
            $form_params = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'refresh_token' => $existingRefreshToken,
                'grant_type' => 'refresh_token',
            ];
            if (Tool::config('app_type') == 'cn') $form_params = array_add($form_params, 'resource', Constants::REST_ENDPOINT_21V);
            $response = $client->post($this->access_token_url, [
                'form_params' => $form_params,
            ]);
            $token = json_decode($response->getBody()->getContents(), true);
            $access_token = $token['access_token'];
            $refresh_token = $token['refresh_token'];
            $expires = $token['expires_in'] != 0 ? time() + $token['expires_in'] : 0;
            $data = [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'access_token_expires' => $expires
            ];
            $saved = Tool::updateConfig($data);
            $this->call('cache:clear');
            if (!$saved) $this->error('Refresh Token Error');
            exit;
        } catch (ClientException $e) {
            $this->error($e->getMessage());
            exit;
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
