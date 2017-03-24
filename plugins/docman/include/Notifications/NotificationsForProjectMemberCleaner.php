<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Docman\Notifications;

use Docman_ItemFactory;
use Docman_NotificationsManager;
use PFUser;
use Project;
use UserManager;

class NotificationsForProjectMemberCleaner
{
    /**
     * @var Docman_ItemFactory
     */
    private $item_factory;
    /**
     * @var Dao
     */
    private $users_to_notify_dao;
    /**
     * @var Docman_NotificationsManager
     */
    private $notifications_manager;
    /**
     * @var UserManager
     */
    private $user_manager;

    public function __construct(
        Docman_ItemFactory $item_factory,
        Docman_NotificationsManager $notifications_manager,
        UserManager $user_manager,
        Dao $users_to_notify_dao
    ) {
        $this->item_factory          = $item_factory;
        $this->users_to_notify_dao   = $users_to_notify_dao;
        $this->notifications_manager = $notifications_manager;
        $this->user_manager          = $user_manager;
    }

    public function cleanNotificationsAfterUserRemoval(Project $project, PFUser $user)
    {
        if ($project->isPublic()) {
            return;
        }

        if ($user->isMember($project->getID())) {
            return;
        }

        $root_item = $this->item_factory->getRoot($project->getID());
        if (! $root_item) {
            return;
        }

        $dar = $this->notifications_manager->listAllMonitoredItems($project->getID(), $user->getId());
        if ($dar && ! $dar->isError()) {
            foreach ($dar as $row) {
                $this->notifications_manager->removeUser($row['user_id'], $row['item_id'], $row['type']);
            }
        }
    }

    public function cleanNotificationsAfterProjectVisibilityChange(Project $project, $new_access)
    {
        if ($new_access !== Project::ACCESS_PRIVATE) {
            return;
        }

        $root_item = $this->item_factory->getRoot($project->getID());
        if (! $root_item) {
            return;
        }

        $dar = $this->notifications_manager->listAllMonitoredItems($project->getID());
        if ($dar && ! $dar->isError()) {
            foreach ($dar as $row) {
                $user = $this->user_manager->getUserById($row['user_id']);
                if (! $user->isMember($project->getID())) {
                    $this->notifications_manager->removeUser($row['user_id'], $row['item_id'], $row['type']);
                }
            }
        }
    }
}