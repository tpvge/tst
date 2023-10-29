<?php

/**
 * Класс для получения данных о пользователе GitHub через API.
 */
class GitHubUser
{
    private $username; // Логин пользователя GitHub
    private $validFields; // Массив полей, к которым можно обращаться
    private $userData; // Данные о пользователе

    /**
     * Конструктор класса.
     *
     * @param string $username     Логин пользователя GitHub.
     * @param array  $validFields  Массив полей, к которым можно обращаться.
     */
    public function __construct($username, $validFields = [])
    {
        $this->username = $username;
        $this->validFields = $validFields;
    }



    /**
     * Метод для обновления даты последнего обращения и сохранения ее в куки.
     */
    public static function updateLastAccessDateInCookies()
    {
        $currentDate = date('Y-m-d H:i:s'); // Получаем текущую дату и время
        setcookie('github_last_access', $currentDate, time() + 86400); // Устанавливаем куки на сутки
    }



    /**
     * Получает данные о пользователе GitHub через API.
     *
     * @return $this
     * @throws Exception
     */
    public function getUserData()
    {
        if ($this->userData === null) {
            if (isset($_COOKIE["github_last_access"]) && (time() - strtotime($_COOKIE["github_last_access"]) < 3600)) {
                $this->userData = $_SESSION['github_user_data'];
                echo "Данные взяты с сессии </br>";
            } else {
                $api_url = "https://api.github.com/users/{$this->username}";

                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: MyApp']);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('Ошибка CURL: ' . curl_error($ch));
                }

                curl_close($ch);

                $data = json_decode($response);

                if (!$data) {
                    throw new Exception('Не удалось получить данные пользователя.');
                }
                $_SESSION['github_user_data'] = $data;
                $this->userData = $data;
                echo "Данные взяты с API </br>";
                self::updateLastAccessDateInCookies();
            }
        }

        return $this;
    }




    /**
     * Магический геттер для доступа к полям объекта.
     *
     * @param string $name  Имя поля.
     *
     * @return mixed|null  Значение поля или null, если поле не существует.
     */
    public function __get($name)
    {
        if (property_exists($this->userData, $name)) {
            return $this->validateField($name, $this->userData->$name);
        } else {
            return null;
        }
    }

/**
     * Статический метод для получения массива всех данных о пользователе GitHub.
     *
     * @param string $username     Логин пользователя GitHub.
     * @param array  $validFields  Массив полей, к которым можно обращаться.
     *
     * @return array|null  Массив данных о пользователе или null в случае ошибки.
     */
    public static function getAllUserData($username, $validFields = []) {
        $user = new self($username, $validFields);

        try {
            $user->getUserData();
            return $user->userData;
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * Валидация и явное приведение типов для заданных полей.
     *
     * @param string $field  Имя поля.
     * @param mixed  $value  Значение поля.
     *
     * @return mixed  Валидированное значение поля.
     */
    private function validateField($field, $value)
    {
        switch ($field) {
            case 'public_repos':
                return (int)htmlspecialchars($value);
            case 'followers':
                return (int)htmlspecialchars($value);
            case 'following':
                return (int)htmlspecialchars($value);
            default:
                return (string)htmlspecialchars($value);
        }
    }




    /**
     * Метод для получения HTML-кода карточки профиля пользователя GitHub.
     *
     * @return string  HTML-код карточки профиля пользователя.
     * @throws Exception
     */
    public function getUserProfileCardHtml()
    {
        ob_start(); // Включаем буферизацию вывода
?>
        <div class="container">
            <div class="card">
                <img src="<?= $this->avatar_url; ?>" alt="Person" class="card__image">
                <p class="card__name"><?= $this->name; ?></p>
                <p class="card__logo"><?= $this->login; ?></p>
                <div class="grid-container">
                    <div class="grid-child-posts">
                        followers: <?= $this->followers; ?>
                    </div>
                    <div class="grid-child-followers">
                        following: <?= $this->following; ?>
                    </div>
                    <div class="grid-child-followers">
                        repos: <?= $this->public_repos; ?>
                    </div>
                </div>
            </div>
    <?php

        $html = ob_get_contents(); // Получаем содержимое буфера
        ob_end_clean(); // Очищаем буфер

        return $html;
    }





    /**
     * Статический метод для получения даты последнего обращения к GitHub из кук.
     *
     * @return string|null  Дата последнего обращения или null, если куки не установлены.
     */
    public static function getLastAccessDateFromCookies()
    {
        if (isset($_COOKIE['github_last_access'])) {
            return $_COOKIE['github_last_access'];
        }

        return null;
    }
}
