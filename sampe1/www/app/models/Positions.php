<?php

class Positions extends Phalcon\Mvc\Model
{

    protected $account_id;

    protected $latitude;

    protected $longitude;

    protected $country;

    public static function setPosition($account_id, $latitude, $longitude, $country)
    {
        if ($longitude > 180 || $longitude < - 180 || $latitude > 90 || $latitude < - 90) {
            return;
        }
        $pos = static::findFirstByAccountId($account_id);
        if (! $pos) {
            $pos = new static();
            $pos->account_id = $account_id;
        }
        $pos->longitude = $longitude;
        $pos->latitude = $latitude;
        $pos->country = $country;
        $pos->save();
        return $pos;
    }

    public static function searchNearBy($latitute, $longitude, $degree, $max_rows = 60)
    {
        $model = new static();
        $mm = $model->getModelsManager();
        $queryBuilder = $mm->createBuilder();
        $queryBuilder->from('Positions')
            ->join('UserAccounts', 'UserAccounts.account_id = Positions.account_id')
            ->columns(array(
            'Positions.account_id',
            'Positions.latitude',
            'Positions.longitude',
            'ABS( latitude - :lat:) AS delta_lat',
            'ABS( longitude - :lon: ) AS delta_lon',
            'UserAccounts.nickname',
            'UserAccounts.photo'
        ))
            ->orderBy('delta_lat ASC, delta_lon ASC')
            ->where('ABS( latitude - :lat:) <= :degree: AND ABS( longitude - :lon: ) <= :degree:')
            ->limit($max_rows);
        $query = $queryBuilder->getQuery();
        return $query->execute(array(
            'lat' => $latitute,
            'lon' => $longitude,
            'degree' => $degree
        ))->toArray();
    }
}