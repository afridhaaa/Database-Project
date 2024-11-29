<?php include 'db/db.php';
if (isset($_POST['avgPoint'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'constructor' => '$constructor_info.constructor_name'
                ],
                'avg_points' => ['$avg' => '$points']
            ]
        ],
        [
            '$sort' => ['avg_points' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:avgpoint.php?msg=avg_point");
    } else {
        header("Location:index.php?msg=avgpoint_error");
    }
}

if (isset($_POST['raceswon'])) {
    $pipeline = [
        [
            '$match' => ['position' => 1]
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$project' => [
                'driver' => '$driver_info.forename',
                'race_name' => '$race_info.name',
                'circuit_name' => '$circuit_info.circuit_name'
            ]
        ],
        [
            '$sort' => ['driver' => 1, 'race_info.date' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:raceswon.php?msg=races_won");
    } else {
        header("Location:index.php?msg=raceswon_error");
    }
}

if (isset($_POST['topdriver'])) {
    $pipeline = [
        [
            '$match' => ['position' => 1]
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'country' => '$circuit_info.circuit_country'
                ],
                'total_wins' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['total_wins' => -1]
        ],
        [
            '$limit' => 3
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:topdriver.php?msg=top_driver");
    } else {
        header("Location:index.php?msg=topdriver_error");
    }
}

if (isset($_POST['retrieveraces'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$match' => ['$expr' => ['$lt' => ['$position', '$grid']]]
        ],
        [
            '$project' => [
                'driver' => '$driver_info.forename',
                'race_name' => '$race_info.name',
                'starting_position' => '$grid',
                'final_position' => '$position',
                'constructor' => '$constructor_info.constructor_name'
            ]
        ],
        [
            '$sort' => ['position' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:retrieveraces.php?msg=retrieve_races");
    } else {
        header("Location:index.php?msg=retrieveraces_error");
    }
}

if (isset($_POST['totalpoints'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'constructor' => '$constructor_info.constructor_name',
                    'circuit' => '$circuit_info.circuit_name'
                ],
                'total_points' => ['$sum' => '$points']
            ]
        ],
        [
            '$sort' => ['total_points' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:totalpoints.php?msg=total_points");
    } else {
        header("Location:index.php?msg=totalpoints_error");
    }
}

if (isset($_POST['laptime'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'race_name' => '$race_info.name',
                    'circuit_name' => '$circuit_info.circuit_name'
                ],
                'fastest_lap_time' => ['$min' => '$fastestLapTime']
            ]
        ],
        [
            '$sort' => ['fastest_lap_time' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:laptime.php?msg=lap_time");
    } else {
        header("Location:index.php?msg=laptime_error");
    }
}

if (isset($_POST['alldrivers'])) {
    $pipeline = [
        [
            '$match' => [
                'position' => 1,
                'constructorId' => ['$in' => $db->constructors->find(['no_of_titles' => ['$gt' => 0]], ['projection' => ['constructor_id' => 1]])->toArray()]
            ]
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$project' => [
                'driver' => '$driver_info.forename',
                'constructor' => '$constructor_info.constructor_name',
                'race_name' => '$race_info.name',
                'circuit_name' => '$circuit_info.circuit_name'
            ]
        ],
        [
            '$sort' => ['constructor_info.constructor_name' => 1, 'race_info.date' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:alldrivers.php?msg=all_drivers");
    } else {
        header("Location:index.php?msg=alldrivers_error");
    }
}

if (isset($_POST['dwithfast'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$match' => [
                'constructorId' => ['$in' => $db->results->find(['position' => ['$lte' => 3]], ['projection' => ['constructorId' => 1]])->toArray()]
            ]
        ],
        [
            '$project' => [
                'driver' => '$driver_info.forename',
                'constructor' => '$constructor_info.constructor_name',
                'race_name' => '$race_info.name',
                'fastest_lap_speed' => '$fastestLapSpeed'
            ]
        ],
        [
            '$sort' => ['fastestLapSpeed' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:dwithfast.php?msg=dwith_fast");
    } else {
        header("Location:index.php?msg=dwithfast_error");
    }
}

if (isset($_POST['raceslist'])) {
    $pipeline = [
        [
            '$match' => ['position' => 1]
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$match' => ['race_info.year' => 2015]
        ],
        [
            '$project' => [
                'race_name' => '$race_info.name',
                'circuit_name' => '$circuit_info.circuit_name',
                'winner' => '$driver_info.forename'
            ]
        ],
        [
            '$sort' => ['race_info.date' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:raceslist.php?msg=races_list");
    } else {
        header("Location:index.php?msg=raceslist_error");
    }
}

if (isset($_POST['retrievedrivers'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'constructor' => '$constructor_info.constructor_name',
                    'circuit' => '$circuit_info.circuit_name'
                ],
                'total_points' => ['$sum' => '$points']
            ]
        ],
        [
            '$match' => ['total_points' => ['$gt' => 50]]
        ],
        [
            '$sort' => ['total_points' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:retrievedrivers.php?msg=retrieve_drivers");
    } else {
        header("Location:index.php?msg=retrievedrivers_error");
    }
}

if (isset($_POST['dwithavgspeed'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'circuit_name' => '$circuit_info.circuit_name',
                    'driver' => '$driver_info.forename'
                ],
                'avg_speed' => ['$avg' => '$milliseconds']
            ]
        ],
        [
            '$sort' => ['avg_speed' => 1]
        ]
    ];

    $query = $db->lap_times->aggregate($pipeline);

    if ($query) {
        header("Location:dwithavgspeed.php?msg=dwith_avgspeed");
    } else {
        header("Location:index.php?msg=dwithavgspeed_error");
    }
}

if (isset($_POST['raceswithmdrivers'])) {
    $pipeline = [
        [
            '$match' => ['position' => ['$lte' => 5]]
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'race_name' => '$race_info.name',
                    'constructor_name' => '$constructor_info.constructor_name'
                ],
                'drivers_in_top_5' => ['$sum' => 1]
            ]
        ],
        [
            '$match' => ['drivers_in_top_5' => ['$gt' => 1]]
        ],
        [
            '$sort' => ['drivers_in_top_5' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:raceswithmdrivers.php?msg=raceswith_mdrivers");
    } else {
        header("Location:index.php?msg=raceswithmdrivers_error");
    }
}

if (isset($_POST['mostracewins'])) {
    $pipeline = [
        [
            '$match' => ['position' => 1]
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'circuit_name' => '$circuit_info.circuit_name'
                ],
                'total_wins' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['total_wins' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:mostracewins.php?msg=mostrace_wins");
    } else {
        header("Location:index.php?msg=mostracewins_error");
    }
}

if (isset($_POST['allraces'])) {
    $pipeline = [
        [
            '$match' => [
                'fastestLapTime' => ['$ne' => null],
                'position' => ['$ne' => 1]
            ]
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$project' => [
                'race_name' => '$race_info.name',
                'driver' => '$driver_info.forename',
                'fastest_lap_time' => '$fastestLapTime',
                'position' => '$position'
            ]
        ],
        [
            '$sort' => ['fastestLapTime' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:allraces.php?msg=all_races");
    } else {
        header("Location:index.php?msg=allraces_error");
    }
}

if (isset($_POST['top5'])) {
    $pipeline = [
        [
            '$match' => ['position' => ['$lte' => 3]]
        ],
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'driver' => '$driver_info.forename',
                    'constructor' => '$constructor_info.constructor_name'
                ],
                'podium_finishes' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['podium_finishes' => -1]
        ],
        [
            '$limit' => 5
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:top5.php?msg=top_5");
    } else {
        header("Location:index.php?msg=top_5_error");
    }
}

if (isset($_POST['cwithmost'])) {
    try {
        $pipeline = [
            [
                '$match' => ['position' => 1]
            ],
            [
                '$lookup' => [
                    'from' => 'constructors',
                    'localField' => 'constructorId',
                    'foreignField' => 'constructor_id',
                    'as' => 'constructor_info'
                ]
            ],
            [
                '$unwind' => '$constructor_info'
            ],
            [
                '$lookup' => [
                    'from' => 'races',
                    'localField' => 'raceId',
                    'foreignField' => 'raceId',
                    'as' => 'race_info'
                ]
            ],
            [
                '$unwind' => '$race_info'
            ],
            [
                '$lookup' => [
                    'from' => 'circuits',
                    'localField' => 'race_info.circuit_id',
                    'foreignField' => 'circuit_id',
                    'as' => 'circuit_info'
                ]
            ],
            [
                '$unwind' => '$circuit_info'
            ],
            [
                '$group' => [
                    '_id' => [
                        'constructor_name' => '$constructor_info.constructor_name',
                        'circuit_name' => '$circuit_info.circuit_name'
                    ],
                    'total_wins' => ['$sum' => 1]
                ]
            ],
            [
                '$sort' => ['total_wins' => -1]
            ]
        ];

        $query = $db->results->aggregate($pipeline);
        $results = iterator_to_array($query);

        if (!empty($results)) {
            header("Location:cwithmost.php?msg=cwith_most");
        } else {
            header("Location:cwithmost.php?msg=no_results");
        }
    } catch (MongoDB\Exception\Exception $e) {
        // Log the error message or display it
        error_log($e->getMessage());
        header("Location:index.php?msg=cwithmost_error");
    }
}


if (isset($_POST['nofraces'])) {
    // Retrieve sort order and search keyword from POST data
    $sort_order = isset($_POST['sort_order']) ? $_POST['sort_order'] : 'DESC';
    $search_keyword = isset($_POST['search']) ? $_POST['search'] : '';

    // Build MongoDB Aggregation Pipeline
    $pipeline = [];

    // If there is a search keyword, add a match stage to the pipeline
    if (!empty($search_keyword)) {
        $pipeline[] = [
            '$match' => [
                '$or' => [
                    ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],  // Search by driver name
                    ['race_info.year' => (int)$search_keyword]  // Search by year (convert to integer)
                ]
            ]
        ];
    }

    // Join drivers collection
    $pipeline[] = [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ];
    $pipeline[] = ['$unwind' => '$driver_info'];

    // Join races collection
    $pipeline[] = [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ];
    $pipeline[] = ['$unwind' => '$race_info'];

    // Group by driver and year, and calculate total races and average points
    $pipeline[] = [
        '$group' => [
            '_id' => [
                'driver' => '$driver_info.forename',
                'year' => '$race_info.year'
            ],
            'total_races' => ['$sum' => 1],
            'avg_points' => ['$avg' => '$points']
        ]
    ];

    // Apply sort order
    $pipeline[] = ['$sort' => ['avg_points' => ($sort_order == 'DESC' ? -1 : 1)]];

    // Execute aggregation query
    $query = $db->results->aggregate($pipeline);

    // Redirect to the main page with a message if the query executed successfully
    if ($query) {
        header("Location:nofraces.php?msg=nof_races");
    } else {
        header("Location:index.php?msg=nofraces_error");
    }
}


if (isset($_POST['clist'])) {
    // Set the sort order based on the POST value, defaulting to descending (-1)
    $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] == 'ASC' ? 1 : -1;

    $pipeline = [
        [
            '$match' => ['fastestLap' => ['$ne' => null]]
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'constructor_name' => '$constructor_info.constructor_name',
                    'circuit_name' => '$circuit_info.circuit_name'
                ],
                'total_fastest_laps' => ['$sum' => 1]
            ]
        ],
        [
            '$match' => ['total_fastest_laps' => ['$gte' => 5]]
        ],
        // Apply dynamic sort order based on POST data
        [
            '$sort' => ['total_fastest_laps' => $sort_order]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:clist.php?msg=c_list");
    } else {
        header("Location:index.php?msg=clist_error");
    }
}


if (isset($_POST['araces'])) {
    $pipeline = [
        [
            '$match' => ['position' => ['$in' => [1, 2]]]
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'race_name' => '$race_info.name',
                    'constructor_name' => '$constructor_info.constructor_name'
                ],
                'drivers_in_top_2' => ['$sum' => 1]
            ]
        ],
        [
            '$match' => ['drivers_in_top_2' => 2]
        ],
        [
            '$sort' => ['race_info.date' => 1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:araces.php?msg=a_races");
    } else {
        header("Location:index.php?msg=araces_error");
    }
}

if (isset($_POST['avgn'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'circuit_name' => '$circuit_info.circuit_name',
                    'driver' => '$driver_info.forename'
                ],
                'avg_laps' => ['$avg' => '$laps']
            ]
        ],
        [
            '$sort' => ['avg_laps' => -1]
        ]
    ];

    $query = $db->results->aggregate($pipeline);

    if ($query) {
        header("Location:avgn.php?msg=avg_n");
    } else {
        header("Location:index.php?msg=avgn_error");
    }
}

if (isset($_POST['cona'])) {
    // Set up MongoDB pagination and sort options
    $results_per_page = 13;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start_from = ($current_page - 1) * $results_per_page;

    // Get search keyword and sort order from URL or set defaults
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
    $sort_direction = ($sort_order === 'DESC') ? -1 : 1;

    // Build MongoDB aggregation pipeline
    $pipeline = [
        [
            '$match' => ['position' => 1]
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$match' => ['circuit_info.circuit_country' => 'Australia']
        ],
        // Search filter based on constructor name if a search keyword is provided
        [
            '$match' => [
                'constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')
            ]
        ],
        [
            '$group' => [
                '_id' => [
                    'constructor_name' => '$constructor_info.constructor_name',
                    'year' => '$race_info.year'
                ],
                'total_wins' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['total_wins' => $sort_direction]
        ],
        [
            '$skip' => $start_from
        ],
        [
            '$limit' => $results_per_page
        ]
    ];

    // Execute the aggregation pipeline
    $query = $db->results->aggregate($pipeline);

    // Check if the query ran successfully
    if ($query) {
        // Redirect to cona.php with a success message and pagination info
        header("Location:cona.php?msg=cona&search=" . urlencode($search_keyword) . "&sort_order=" . urlencode($sort_order) . "&page=" . $current_page);
    } else {
        // Redirect to index.php with an error message if the query failed
        header("Location:index.php?msg=cona_error");
    }
}

if (isset($_POST['rtl'])) {
    // Define the MongoDB aggregation pipeline
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'drivers',
                'localField' => 'driverId',
                'foreignField' => 'driverId',
                'as' => 'driver_info'
            ]
        ],
        [
            '$unwind' => '$driver_info'
        ],
        [
            '$lookup' => [
                'from' => 'constructors',
                'localField' => 'constructorId',
                'foreignField' => 'constructor_id',
                'as' => 'constructor_info'
            ]
        ],
        [
            '$unwind' => '$constructor_info'
        ],
        [
            '$lookup' => [
                'from' => 'races',
                'localField' => 'raceId',
                'foreignField' => 'raceId',
                'as' => 'race_info'
            ]
        ],
        [
            '$unwind' => '$race_info'
        ],
        [
            '$lookup' => [
                'from' => 'circuits',
                'localField' => 'race_info.circuit_id',
                'foreignField' => 'circuit_id',
                'as' => 'circuit_info'
            ]
        ],
        [
            '$unwind' => '$circuit_info'
        ],
        [
            '$group' => [
                '_id' => [
                    'forename' => '$driver_info.forename',
                    'constructor_name' => '$constructor_info.constructor_name',
                    'circuit_name' => '$circuit_info.circuit_name'
                ],
                'total_points' => ['$sum' => '$points']
            ]
        ],
        [
            '$sort' => ['total_points' => -1]
        ]
    ];

    // Execute the aggregation pipeline
    $query = $db->results->aggregate($pipeline);

    // Check if the query ran successfully
    if ($query) {
        // Redirect to rtl.php with a success message
        header("Location:rtl.php?msg=rtl");
    } else {
        // Redirect to index.php with an error message if the query failed
        header("Location:index.php?msg=rtl_error");
    }
}


?>