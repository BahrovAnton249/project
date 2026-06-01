<?php
require_once 'User.php';

class UserController {
    private $user;
    
    public function __construct($pdo) {
        $this->user = new User($pdo);
    }
    
    public function register($data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['error' => 'Все поля обязательны'];
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Некорректный email'];
        }
        if (strlen($data['password']) < 4) {
            return ['error' => 'Пароль должен быть не менее 4 символов'];
        }
        if ($this->user->userExists($data['username'], $data['email'])) {
            return ['error' => 'Пользователь уже существует'];
        }
        if ($this->user->register($data['username'], $data['email'], $data['password'])) {
            return ['success' => true, 'message' => 'Пользователь зарегистрирован'];
        }
        return ['error' => 'Ошибка регистрации'];
    }
    
    public function login($data) {
        if (empty($data['username']) || empty($data['password'])) {
            return ['error' => 'Username и password обязательны'];
        }
        
        $user = $this->user->login($data['username'], $data['password']);
        
        if (!$user) {
            return ['error' => 'Неверное имя пользователя или пароль'];
        }
        
        return [
            'success' => true,
            'message' => 'Авторизация успешна',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ];
    }
    
    public function getAll() {
        return $this->user->getAll();
    }
    
    public function getById($id) {
        if (!is_numeric($id)) {
            return ['error' => 'ID должен быть числом'];
        }
        $user = $this->user->getById($id);
        if ($user) {
            return $user;
        }
        return ['error' => 'Пользователь не найден'];
    }
    
    public function updatePassword($id, $data) {
        if (!is_numeric($id)) {
            return ['error' => 'ID должен быть числом'];
        }
        if (empty($data['password'])) {
            return ['error' => 'Новый пароль обязателен'];
        }
        if (strlen($data['password']) < 4) {
            return ['error' => 'Пароль должен быть не менее 4 символов'];
        }
        if (!$this->user->getById($id)) {
            return ['error' => 'Пользователь не найден'];
        }
        if ($this->user->updatePassword($id, $data['password'])) {
            return ['success' => true, 'message' => 'Пароль обновлён'];
        }
        return ['error' => 'Ошибка обновления пароля'];
    }
    
    public function delete($id) {
        if (!is_numeric($id)) {
            return ['error' => 'ID должен быть числом'];
        }
        if (!$this->user->getById($id)) {
            return ['error' => 'Пользователь не найден'];
        }
        if ($this->user->delete($id)) {
            return ['success' => true, 'message' => 'Пользователь удалён'];
        }
        return ['error' => 'Ошибка удаления'];
    }
}
?>