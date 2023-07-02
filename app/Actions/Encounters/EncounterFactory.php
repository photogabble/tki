<?php declare(strict_types=1);
/**
 * app/Actions/EncounterFactory.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2023 Simon Dann, The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki\Actions\Encounters;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Illuminate\Container\Container;
use Exception;
use Tki\Models\Encounter;

abstract class EncounterFactory
{
    protected Encounter $model;

    public function __construct(Encounter $model)
    {
        $this->model = $model;
    }

    abstract public function actions(): array;

    public function has(string $action): bool
    {
        return in_array($action, array_keys($this->actions()));
    }

    /**
     * @param string $action
     * @return EncounterActivity
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function make(string $action): EncounterActivity
    {
        if (!$this->has($action)) {
            throw new Exception("Invalid Encounter action [$action]");
        }
        /** @var EncounterActivity $class */
        $class = Container::getInstance()->get($this->actions()[$action]);
        $class->setEncounter($this->model);

        return $class;
    }

    /**
     * @param string $action
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function do(string $action): bool
    {
        return $this->make($action)->execute();
    }
}