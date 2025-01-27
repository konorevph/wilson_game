<?php

class Handler
{
	private static array $patterns = [
		'/users/login',
		'/users/register',
		'/users/logout',
		'/users/verify-token',
		'/games',
		'/games/create',
		'/games/{game_id}',
		'/games/{game_id}/join',
		'/games/{game_id}/leave',
		'/games/make_move',
	];
	private PDO $db;
	private array $jsonBody;
	private array $params;

	public function __construct(PDO $db) {
		$this->db = $db;
	}

	public function getData(): array
	{
		$uri = $this->matchUriPattern(_URI);

		if ($uri === null)
		{
			return [
				'status' => 'error',
				'message' => 'Not Found'
			];
		}
		$pattern = $uri['pattern'];
		$this->params = $uri['params'];

		$body = file_get_contents('php://input', true);
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($body))
		{
			$this->jsonBody = json_decode($body, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				return [
					'status' => 'error',
					'message' => 'Invalid JSON'
				];
			};
		}

		switch ($pattern)
		{
			case '/users/login':
				return $this->login();
			case '/users/register':
				return $this->register();
			case '/users/logout':
				return $this->logout();
			case '/users/verify-token':
				return $this->verifyToken();
			case '/games':
				return $this->getGames();
			case '/games/create':
				return $this->createGame();
			case '/games/{game_id}':
				return $this->getGame();
			case '/games/{game_id}/join':
				return $this->joinGame();
			case '/games/{game_id}/leave':
				return $this->leaveGame();
			case '/games/make_move':
				return $this->makeMove();
			default:
				return $this->methodNotFoundResponce();
		}
	}

	private function register(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$login = $this->jsonBody['login'] ?? '';
		$password = $this->jsonBody['password'] ?? '';
		if (empty($login) || empty($password))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('register_user', [
			'login' => $login,
			'password' => $password,
		]);
	}

	private function login(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$login = $this->jsonBody['login'] ?? '';
		$password = $this->jsonBody['password'] ?? '';
		if (empty($login) || empty($password))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('login_user', [
			'login' => $login,
			'password' => $password,
		]);
	}

	private function logout(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$token = $this->jsonBody['token'] ?? '';
		if (empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('logout_user', [
			'token' => $token,
		]);
	}

	private function verifyToken(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$token = $this->jsonBody['token'] ?? '';
		if (empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('verify_token', [
			'token' => $token,
		]);
	}

	private function getGames(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$token = $this->jsonBody['token'] ?? '';
		if (empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('get_available_games', [
			'token' => $token,
		]);
	}

	private function createGame(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$token = $this->jsonBody['token'] ?? '';
		$is_private = isset($this->jsonBody['is_private']) 
			? filter_var($this->jsonBody['is_private'], FILTER_VALIDATE_BOOLEAN) 
			: null;
		$time = $this->jsonBody['time'] ?? '';
		if (empty($token) || $is_private === null || empty($time))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('create_game', [
			'token' => $token,
			'is_private' => $is_private,
			'time' => $time,
		]);
	}

	private function getGame(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$game_id = $this->params['game_id'];
		$token = $this->jsonBody['token'] ?? '';
		if (empty($game_id) || empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('get_game_status', [
			'token' => $token,
			'game_id' => $game_id,
		]);
	}

	private function joinGame(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$game_id = $this->params['game_id'];
		$token = $this->jsonBody['token'] ?? '';
		$invite_code = $this->jsonBody['invite_code'] ?? '';
		if (empty($game_id) || empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		if (empty($invite_code))
		{
			return $this->selectDBFunction('join_public_game', [
				'token' => $token,
				'game_id' => $game_id,
			]);
		}
		return $this->selectDBFunction('join_private_game', [
			'token' => $token,
			'game_id' => $game_id,
			'invite_code' => $invite_code,
		]);
	}

	private function leaveGame(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$game_id = $this->params['game_id'];
		$token = $this->jsonBody['token'] ?? '';
		if (empty($game_id) || empty($token))
		{
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('leave_game', [
			'token' => $token,
			'game_id' => $game_id,
		]);
	}

	private function makeMove(): array
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
		{
			return $this->methodNotFoundResponce();
		}
		$token = $this->jsonBody['token'] ?? '';
		$player_id = $this->jsonBody['player_id'] ?? '';
		$from_x = $this->jsonBody['from_x'] ?? '';
		$from_y = $this->jsonBody['from_y'] ?? '';
		$to_x = $this->jsonBody['to_x'] ?? '';
		$to_y = $this->jsonBody['to_y'] ?? '';
		$card_id = $this->jsonBody['card_id'] ?? '';
		if (
			empty($token)
			|| empty($player_id)
			|| empty($from_x)
			|| empty($from_y)
			|| empty($to_x)
			|| empty($to_y)
			|| empty($card_id)
		) {
			return [
				'status' => 'error',
				'message' => 'Data is incorrect'
			];
		}
		return $this->selectDBFunction('make_move', [
			'token' => $token,
			'player_id' => $player_id,
			'from_x' => $from_x,
			'from_y' => $from_y,
			'to_x' => $to_x,
			'to_y' => $to_y,
			'card_id' => $card_id,
		]);
	}

	private static function matchUriPattern($uri)
	{
		foreach (self::$patterns as $pattern) {
			if (strpos($pattern, '{') === false) {
				if ($uri === $pattern) {
					return [
						'pattern' => $pattern,
						'params' => []
					];
				}
			} else {
				$regex = preg_replace_callback('/{(\w+)}/', function($matches) {
					return '(?P<' . $matches[1] . '>[\w-]+)';
				}, $pattern);
	
				if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
					$params = [];
					foreach ($matches as $key => $value) {
						if (!is_int($key)) {
							$params[$key] = $value;
						}
					}

					return [
						'pattern' => $pattern,
						'params' => $params
					];
				}
			}
		}

		return null;
	}

	private function selectDBFunction(string $function, array $params): array
	{
		$placeholders = implode(', ', array_map(fn($key) => ":$key", array_keys($params)));
		$stmt = $this->db->prepare("SELECT $function($placeholders)");
		foreach ($params as $key => $value) {
			$stmt->bindValue(":$key", $value, PDO::PARAM_STR);
		}
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_COLUMN);
		return json_decode($result ?? '{}', true);
	}

	private function methodNotFoundResponce(): array
	{
		return [
			'status' => 'error',
			'message' => 'Method not found'
		];
	}
}