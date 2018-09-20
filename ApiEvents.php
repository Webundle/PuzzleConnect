<?php
namespace Puzzle\ConnectBundle;

final class ApiEvents
{
    /**
     * The API_BAD_RESPONSE event occurs after fetching api uri.
     *
     * This means current fetching returns bad response from api
     *
     * @Event("Puzzle\ConnectBundle\Event\ApiResponseEvent")
     */
    const API_BAD_RESPONSE = 'app.api.bad_response';
}