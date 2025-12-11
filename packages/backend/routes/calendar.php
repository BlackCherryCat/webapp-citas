<?php
use App\Models\Appointment;
use App\Models\User;
use App\Services\GoogleService;
use App\Services\GoogleService\GoogleCalendar;
use App\Services\GoogleService\GoogleCalendarEvent;
use App\Services\GoogleService\GoogleCalendarService;
use App\Services\GoogleService\GoogleUser;
use App\Services\UserService;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;
use App\Middlewares\JWTAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


return function (App $app) {
$app->group('/calendar', function (RouteCollectorProxy $group) {

    $group->get('/google-auth', function ($request, $response) {

        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader); // extraer JWT

        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope(Google_Service_Calendar::CALENDAR);

        // Guardar el token JWT en el parámetro `state`
        $client->setState($token);

        $authUrl = $client->createAuthUrl();

        $response->getBody()->write(json_encode(['url' => $authUrl]));
        return $response
        ->withStatus(200)
        ->withHeader('Content-Type', 'application/json');

        })->add(new JWTAuth());

    $group->get('/google-callback', function ($request, $response) {
       	$state = $request->getQueryParams()['state'] ?? null;
       if (!$state) {
           return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
       }

       try {
           $decoded = JWT::decode($state, new Key($_ENV['JWT_SECRET'], 'HS256'));
           $decoded = json_decode(json_encode($decoded), true);
       } catch (\Exception $e) {
           return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
       }

       $user = User::find($decoded['user_id']);

       if (!$user) {
           return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
       }

	$code = $request->getQueryParams()['code'] ?? null;

	if (!$code) {
	$response->getBody()->write(json_encode([
        'error' => 'Código no proporcionado',
    ]));
		return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
	}

	$client = new Google_Client();
	$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
	$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
	$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);

	$user->google_calendar_token = $code;
	$user->save();
	$token = $client->fetchAccessTokenWithAuthCode($code);
	$client->setAccessToken($token);


	$calendarService = new Google_Service_Calendar($client);

$appointments = Appointment::where('host_user_id', $user->id)
    ->orWhere('guest_user_id', $user->id)
    ->get();


foreach ($appointments as $appointment) {
    $start = strtotime($appointment->date);
    $end = $start;

    $event = new Google_Service_Calendar_Event([
        'summary' => $appointment->title,
        'description' => $appointment->description,
        'start' => [
            'dateTime' => date(DATE_RFC3339, $start),
            'timeZone' => 'Europe/Madrid',
        ],
        'end' => [
            'dateTime' => date(DATE_RFC3339, $end),
            'timeZone' => 'Europe/Madrid',
        ],
    ]);

    $calendarService->events->insert('primary', $event);
}

    header("Location: http://localhost:5173/calendar");
    exit;
});

});
};
