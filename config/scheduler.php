<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Turns Per Tick
    |--------------------------------------------------------------------------
    |
    | Update how many turns per tick
    |
    */
    'turns_per_tick' => 6,

    /*
    |--------------------------------------------------------------------------
    | Minutes between ticks
    |--------------------------------------------------------------------------
    |
    | Set this to how often in minutes you are running the scheduler command.
    |
    */
    'sched_ticks' => 1,

    /*
    |--------------------------------------------------------------------------
    | Minutes between New Turns
    |--------------------------------------------------------------------------
    |
    | New turns rate also includes towing, kabal
    |
    */
    'sched_turns' => 2,
    /*
    |--------------------------------------------------------------------------
    | Minutes between Port Production
    |--------------------------------------------------------------------------
    |
    | How often port production occurs
    |
    */
    'sched_ports' => 1,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Planetary Production
    |--------------------------------------------------------------------------
    |
    | How often planet production occurs
    |
    */
    'sched_planets' => 2,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Interest Payments
    |--------------------------------------------------------------------------
    |
    | How often IBANK interests are added
    |
    */
    'sched_ibank' => 2,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Rankings generation
    |--------------------------------------------------------------------------
    |
    | How often rankings will be generated
    |
    */
    'sched_ranking' => 30,

    /*
    |--------------------------------------------------------------------------
    | Minutes between News Generation
    |--------------------------------------------------------------------------
    |
    | How often news are generated
    |
    */
    'sched_news' => 15,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Sector Fighters degradation
    |--------------------------------------------------------------------------
    |
    | How often sector fighters degrade when unsupported by a planet
    |
    */
    'sched_degrade' => 6,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Apocalypse Occurrence
    |--------------------------------------------------------------------------
    |
    | How often apocalypse events will occur
    |
    */
    'sched_apocalypse' => 15,

    /*
    |--------------------------------------------------------------------------
    | Minutes between Governor running
    |--------------------------------------------------------------------------
    |
    | How often the governor will run, cleaning up out-of-bound values
    |
    */
    'sched_thegovernor' => 1,

    /*
    |--------------------------------------------------------------------------
    | Apocalypse Population
    |--------------------------------------------------------------------------
    |
    | Number of colonists a planet needs before being affected by the apocalypse
    |
    */
    'doomsday_value' => 90000000,

    /*
    |--------------------------------------------------------------------------
    | Max Turns
    |--------------------------------------------------------------------------
    |
    | The maximum number of turns a player can receive
    |
    */
    'max_turns' => 2500,
];