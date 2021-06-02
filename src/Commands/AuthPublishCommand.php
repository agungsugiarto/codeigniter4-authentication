<?php

namespace Fluent\Auth\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

use function defined;
use function dirname;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function mkdir;
use function realpath;
use function str_replace;

class AuthPublishCommand extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Auth';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'auth:publish';

    /**
     * the Command's usage description
     *
     * @var string
     */
    protected $usage = 'auth:publish';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Publish auth functionality into the current application.';

    /**
     * the Command's options description
     *
     * @var array
     */
    protected $options = [
        '-f' => 'Force overwrite all existing files in destination',
    ];

    /** @var string */
    protected $sourcePath;

    /**
     * {@inheritdoc}
     */
    public function run(array $params)
    {
        $this->determineSourcePath();

        $this->publishModels();
        $this->publishViews();
        $this->publishNotifications();
        $this->publishAuthServiceProvider();
        $this->publishControllers();
        $this->publishMigration();
        $this->publishConfig();
        $this->publishHashingConfig();
        $this->publishEntities();
        $this->publishLanguage();
    }

    /**
     * Publish migration.
     *
     * @return void
     */
    protected function publishMigration()
    {
        $map = directory_map($this->sourcePath . '/Database/Migrations');

        foreach ($map as $file) {
            $content = file_get_contents("{$this->sourcePath}/Database/Migrations/{$file}");
            $content = $this->replaceNamespace($content, 'Fluent\Auth\Database\Migrations', 'Database\Migrations');

            $this->writeFile("Database/Migrations/{$file}", $content);
        }

        CLI::write('Remember to run `php spark migrate` to migrate the database.', 'yellow');
    }

    /**
     * Publish controller.
     *
     * @return mixed
     */
    protected function publishControllers()
    {
        $map = directory_map($this->sourcePath . '/Controllers/Auth');

        foreach ($map as $file) {
            $content = file_get_contents("{$this->sourcePath}/Controllers/Auth/{$file}");

            $this->writeFile("Controllers/Auth/{$file}", $content);
        }

        $content = file_get_contents("{$this->sourcePath}/Controllers/Home.php");
        $this->writeFile("Controllers/Home.php", $content);
    }

    /**
     * Publish views.
     *
     * @return mixed
     */
    protected function publishViews()
    {
        $map = directory_map($this->sourcePath . '/Views/Auth');

        // Auth view
        foreach ($map as $file) {
            $content = file_get_contents("{$this->sourcePath}/Views/Auth/{$file}");

            $this->writeFile("Views/Auth/{$file}", $content);
        }

        // Email view
        foreach (['layout', 'reset_email', 'verify_email'] as $view) {
            $content = file_get_contents("{$this->sourcePath}/Views/Email/{$view}.php");
            $content = str_replace('Fluent\Auth\Views\Email\layout', 'Email/layout', $content);
            $this->writeFile("Views/Email/{$view}.php", $content);
        }

        foreach (['dashboard', 'welcome_message'] as $view) {
            $content = file_get_contents("{$this->sourcePath}/Views/{$view}.php");
            $this->writeFile("Views/{$view}.php", $content);
        }
    }

    /**
     * Publish notifications.
     *
     * @return void
     */
    protected function publishNotifications()
    {
        $notifications = ['ResetPasswordNotification', 'VerificationNotification'];

        foreach ($notifications as $notif) {
            $path = "{$this->sourcePath}/Notifications/{$notif}.php";

            $content = file_get_contents($path);
            $content = $this->replaceNamespace($content, 'Fluent\Auth\Notifications', 'Notifications');

            $namespace = defined('APP_NAMESPACE') ? APP_NAMESPACE : 'App';
            $content   = str_replace('Fluent\Auth\Notifications', $namespace . 'App\Notifications', $content);
            $content   = str_replace('Fluent\Auth\Views\Email\reset_email', 'Email/reset_email', $content);
            $content   = str_replace('Fluent\Auth\Views\Email\verify_email', 'Email/verify_email', $content);

            $this->writeFile("Notifications/{$notif}.php", $content);
        }
    }

    /**
     * Publish auth service provider.
     *
     * @return void
     */
    protected function publishAuthServiceProvider()
    {
        $path = "{$this->sourcePath}/AuthServiceProvider.php";

        $content = file_get_contents($path);
        $content = $this->replaceNamespace($content, 'Fluent\Auth', 'Providers');

        $this->writeFile("Providers/AuthServiceProvider.php", $content);
    }

    /**
     * Publish model.
     *
     * @return mixed
     */
    protected function publishModels()
    {
        $models = ['UserModel'];

        foreach ($models as $model) {
            $path = "{$this->sourcePath}/Models/{$model}.php";

            $content = file_get_contents($path);
            $content = $this->replaceNamespace($content, 'Fluent\Auth\Models', 'Models');

            $namespace = defined('APP_NAMESPACE') ? APP_NAMESPACE : 'App';
            $content   = str_replace('Fluent\Auth\Entities', $namespace . '\Entities', $content);

            $this->writeFile("Models/{$model}.php", $content);
        }
    }

    /**
     * Publish entities.
     *
     * @return mixed
     */
    protected function publishEntities()
    {
        $path = "{$this->sourcePath}/Entities/User.php";

        $content = file_get_contents($path);
        $content = $this->replaceNamespace($content, 'Fluent\Auth\Entities', 'Entities');

        $this->writeFile("Entities/User.php", $content);
    }

    /**
     * Publish config auth.
     *
     * @return mixed
     */
    protected function publishConfig()
    {
        $path = "{$this->sourcePath}/Config/Auth.php";

        $content = file_get_contents($path);
        $content = str_replace('namespace Fluent\Auth\Config', "namespace Config", $content);
        $content = str_replace("use CodeIgniter\Config\BaseConfig;\n", '', $content);
        $content = str_replace('extends BaseConfig', "extends \Fluent\Auth\Config\Auth", $content);

        $namespace = defined('APP_NAMESPACE') ? APP_NAMESPACE : 'App';
        $content   = str_replace('Fluent\Auth\Models', $namespace . '\Models', $content);

        $this->writeFile("Config/Auth.php", $content);
    }

    /**
     * Publish config hashing.
     *
     * @return mixed
     */
    protected function publishHashingConfig()
    {
        $path = "{$this->sourcePath}/Config/Hashing.php";

        $content = file_get_contents($path);
        $content = str_replace('namespace Fluent\Auth\Config', "namespace Config", $content);
        $content = str_replace("use CodeIgniter\Config\BaseConfig;\n\n", '', $content);
        $content = str_replace('extends BaseConfig', "extends \Fluent\Auth\Config\Hashing", $content);

        $this->writeFile("Config/Hashing.php", $content);
    }

    /**
     * Publish language
     *
     * @return mixed
     */
    protected function publishLanguage()
    {
        $languages = ['Auth', 'Passwords'];

        foreach ($languages as $language) {
            $path = "{$this->sourcePath}/Language/en/{$language}.php";

            $content = file_get_contents($path);
            $this->writeFile("Language/en/{$language}.php", $content);
        }
    }

    /**
     * Replaces the Myth\Auth namespace in the published
     * file with the applications current namespace.
     */
    protected function replaceNamespace(string $contents, string $originalNamespace, string $newNamespace): string
    {
        $appNamespace      = APP_NAMESPACE;
        $originalNamespace = "namespace {$originalNamespace}";
        $newNamespace      = "namespace {$appNamespace}\\{$newNamespace}";

        return str_replace($originalNamespace, $newNamespace, $contents);
    }

    /**
     * Determines the current source path from which all other files are located.
     *
     * @return mixed
     */
    protected function determineSourcePath()
    {
        $this->sourcePath = realpath(__DIR__ . '/../');

        if ($this->sourcePath === '/' || empty($this->sourcePath)) {
            CLI::error('Unable to determine the correct source directory. Bailing.');
            exit();
        }
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @return mixed
     */
    protected function writeFile(string $path, string $content)
    {
        $config  = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];

        $filename  = $appPath . $path;
        $directory = dirname($filename);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($filename)) {
            $overwrite = (bool) CLI::getOption('f');

            if (! $overwrite && CLI::prompt("File '{$path}' already exists in destination. Overwrite?", ['n', 'y']) === 'n') {
                CLI::error("Skipped {$path}. If you wish to overwrite, please use the '-f' option or reply 'y' to the prompt.");
                return;
            }
        }

        if (write_file($filename, $content)) {
            CLI::write(CLI::color('Created: ', 'green') . $path);
        } else {
            CLI::error("Error creating {$path}.");
        }
    }
}
