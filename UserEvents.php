<?php
namespace Puzzle\ConnectBundle;

final class UserEvents
{
    const USER_CREATING = 'puzzle.connect.user_creating';
    const USER_CREATED = 'puzzle.connect.user_created';
    const USER_PASSWORD = 'puzzle.connect.user_update_password';
    
    const USER_UPDATING = 'puzzle.connect.user_updating';
    const USER_UPDATED = 'puzzle.connect.user_updated';
}