<?php

namespace App\Commands\OneDrive;

use App\Helpers\Constants;
use App\Helpers\Tool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Login extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'login';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Account Login';

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
        $this->authorize_url = Tool::config('app_type', 'com') === 'com'
            ? Constants::AUTHORITY_URL.Constants::AUTHORIZE_ENDPOINT
            : Constants::AUTHORITY_URL_21V.Constants::AUTHORIZE_ENDPOINT_21V;
        $this->access_token_url = Tool::config('app_type', 'com') === 'com'
            ? Constants::AUTHORITY_URL.Constants::TOKEN_ENDPOINT
            : Constants::AUTHORITY_URL_21V.Constants::TOKEN_ENDPOINT_21V;
        $this->scopes = Constants::SCOPES;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Tool::hasConfig()) {
            $this->call('install');
        }
        if (Tool::hasBind()) {
            $this->error('Already bind account');
            exit;
        }
        $values = [
            'client_id'     => $this->client_id,
            'redirect_uri'  => $this->redirect_uri,
            'scope'         => $this->scopes,
            'response_type' => 'code',
        ];
        $query = http_build_query($values, '', '&', PHP_QUERY_RFC3986);
        $authorizationUrl = $this->authorize_url."?{$query}";
        $this->info("Please copy this link to your browser to open.\n{$authorizationUrl}");
        $code = $this->ask('Please enter the code obtained by the browser.');
        try {
            $client = new Client();
            $form_params = [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri'  => $this->redirect_uri,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
            ];
            if (Tool::config('app_type', 'com') === 'cn') {
                $form_params = array_add($form_params, 'resource',
                    Constants::REST_ENDPOINT_21V);
            }
            $response = $client->post($this->access_token_url, [
                'form_params' => $form_params,
            ]);
            $token = json_decode($response->getBody()->getContents(), true);
            $access_token = $token['access_token'];
            $refresh_token = $token['refresh_token'];
            $expires = $token['expires_in'] != 0 ? time() + $token['expires_in']
                : 0;
            $data = [
                'access_token'         => $access_token,
                'refresh_token'        => $refresh_token,
                'access_token_expires' => $expires,
            ];
            Tool::updateConfig($data);
            $this->call('cache:clear');
            $this->info('Login Success!');
        } catch (ClientException $e) {
            $this->warn($e->getMessage());
            exit;
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
