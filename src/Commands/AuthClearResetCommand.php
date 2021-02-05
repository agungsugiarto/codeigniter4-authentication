<?php

namespace Fluent\Auth\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;
use Fluent\Auth\Config\Services;

class AuthClearResetCommand extends BaseCommand
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
    protected $name = 'auth:clear-reset';

    /**
     * the Command's usage description
     *
     * @var string
     */
    protected $usage = 'auth:clear-reset';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Delete expired password reset tokens.';

    /**
     * the Command's options description
     *
     * @var array
     */
    protected $options = [
        '--name' => 'Password broker instance by name.',
    ];

    /**
     * {@inheritdoc}
     */
    public function run(array $params)
    {
        try {
            Services::passwords()->broker(CLI::getOption('name'))->getRepository()->destroyExpired();
        } catch (Exception $e) {
            return CLI::error($e->getMessage());
        }

        return CLI::write(CLI::color('Expired reset tokens cleared!', 'green'));
    }
}
