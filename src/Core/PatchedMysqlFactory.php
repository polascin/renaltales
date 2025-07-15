<?php

declare(strict_types=1);

namespace RenalTales\Core;

use React\EventLoop\LoopInterface;
use React\MySQL\Factory;
use React\Socket\ConnectorInterface;

/**
 * Patched MySQL Factory
 *
 * This class extends the React\MySQL\Factory to fix deprecation warnings
 * by explicitly marking nullable parameters.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class PatchedMysqlFactory extends Factory
{
    /**
     * Constructor with properly typed nullable parameters
     *
     * @param ?LoopInterface $loop
     * @param ?ConnectorInterface $connector
     */
    public function __construct(?LoopInterface $loop = null, ?ConnectorInterface $connector = null)
    {
        parent::__construct($loop, $connector);
    }
}
