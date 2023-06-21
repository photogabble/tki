<?php declare(strict_types = 1);
/**
 * ranking.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
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


namespace Tki\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\In;
use Inertia\Inertia;
use Tki\Http\Resources\PlayerRankingResource;
use Tki\Http\Resources\TeamRankingResource;
use Tki\Models\Team;
use Tki\Models\User;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $availablePlayerSorts = ['score', 'turns', 'login', 'good', 'bad', 'efficiency'];
        $availableTeamSorts = ['score', 'members', 'login', 'good', 'bad', 'efficiency'];

        $this->validate($request, [
            'sort_players_by' => ['sometimes', new In($availablePlayerSorts)],
            'sort_players_direction' => ['sometimes', new In(['ASC', 'DESC', 'asc', 'desc'])],
            'sort_teams_by' => ['sometimes', new In($availableTeamSorts)],
            'sort_teams_direction' => ['sometimes', new In(['ASC', 'DESC', 'asc', 'desc'])],
        ]);

        //
        // Team Ranking
        //

        $teamRankingQuery = Team::query()
            ->leftJoin('users', 'users.team_id', '=', 'teams.id')
            ->leftJoin('ships', 'users.ship_id', '=', 'ships.id')
            ->groupBy('teams.id')
            ->select(
                'teams.name',
                DB::raw('COUNT(users.id) as player_count'),
                DB::raw('SUM(users.turns_used) as turns_used_sum'),
                DB::raw('SUM(users.score) as score_sum'),
                DB::raw('SUM(ships.rating) as rating_sum'),
                DB::raw('if (SUM(users.turns_used) < 150, 0, ROUND(SUM(users.score)/SUM(users.turns_used))) as efficiency')
            );

        $sortTeamDirection = strtoupper($request->get('sort_teams_direction', 'DESC'));

        switch($request->get('sort_teams_by')) {
            case 'turns':
                $teamRankingQuery
                    ->orderBy('turns_used_sum', $sortTeamDirection)
                    ->orderBy('name', 'ASC');
                break;
            case 'members':
                $teamRankingQuery
                    ->orderBy('player_count', $sortTeamDirection)
                    ->orderBy('name', 'ASC');
                break;
            case 'good':
                $teamRankingQuery
                    ->orderBy('rating_sum', 'DESC')
                    ->orderBy('name', 'ASC');
                break;
            case 'bad':
                $teamRankingQuery
                    ->orderBy('rating_sum', 'ASC')
                    ->orderBy('name', 'ASC');
            case 'efficiency':
                $teamRankingQuery
                    ->orderBy('efficiency', $sortTeamDirection);
                break;
            default:
                $teamRankingQuery
                    ->orderBy('score_sum', $sortTeamDirection)
                    ->orderBy('name', 'ASC');
        }

        //
        // Player Ranking
        //

        $playerRankingQuery = User::with('team')
            ->join('ships', 'users.ship_id', '=', 'ships.id')
            ->where('ships.ship_destroyed', false)
            ->where('turns_used', '>', 0)
            ->select([
                'name',
                'turns_used',
                'users.score',
                'last_login',
                'ships.rating',
                DB::raw('if (turns_used < 150, 0, ROUND(users.score/turns_used)) as efficiency')
            ]);

        $sortPlayerDirection = strtoupper($request->get('sort_players_direction', 'DESC'));

        switch($request->get('sort_players_by')) {
            case 'turns':
                $playerRankingQuery
                    ->orderBy('turns_used', $sortPlayerDirection)
                    ->orderBy('name', 'ASC');
                break;
            case 'login':
                $playerRankingQuery
                    ->orderBy('last_login', $sortPlayerDirection)
                    ->orderBy('name', 'ASC');
                break;
            case 'good':
                $playerRankingQuery
                    ->orderBy('rating', 'DESC')
                    ->orderBy('name', 'ASC');
                break;
            case 'bad':
                $playerRankingQuery
                    ->orderBy('rating', 'ASC')
                    ->orderBy('name', 'ASC');
            case 'efficiency':
                $playerRankingQuery
                    ->orderBy('efficiency', $sortPlayerDirection);
                break;
            default:
                $playerRankingQuery
                    ->orderBy('score', $sortPlayerDirection)
                    ->orderBy('name', 'ASC');
        }

        return Inertia::render('Ranking', [
            'player' => [
                'sorts' => $availablePlayerSorts,
                'sorting_by' => $request->get('sort_players_by', 'score'),
                'sorting_direction' => $sortPlayerDirection,
                'ranking' => PlayerRankingResource::collection($playerRankingQuery->paginate(25, ['*'], 'player_page')->withQueryString()),
            ],
            'team' => [
                'sorts' => $availableTeamSorts,
                'sorting_by' => $request->get('sort_teams_by', 'score'),
                'sorting_direction' => $sortTeamDirection,
                'ranking' => TeamRankingResource::collection($teamRankingQuery->paginate(25)->withQueryString()),
            ],
        ]);
    }
}
