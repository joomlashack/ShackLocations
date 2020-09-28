<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of ShackLocations.
 *
 * ShackLocations is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * ShackLocations is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ShackLocations.  If not, see <https://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;

class FocalpointControllerLocation extends FormController
{
    protected $view_list = 'locations';

    /**
     * @inheritDoc
     */
    public function save($key = null, $urlVar = null)
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        /**
         * @var FocalpointModelLocation $model
         * @var FocalpointTablelocation $table
         */
        $app     = Factory::getApplication();
        $lang    = Factory::getLanguage();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = $app->input->get('jform', [], 'array');
        $checkin = property_exists($table, 'checked_out');
        $context = "{$this->option}.edit.{$this->context}";
        $task    = $this->getTask();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $app->input->getInt($urlVar);

        $redirectToList = Route::_(
            sprintf(
                'index.php?option=%s&view=%s' . $this->getRedirectToListAppend(),
                $this->option,
                $this->view_list
            ),
            false
        );

        if (!$this->checkEditId($context, $recordId)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect($redirectToList);

            return false;
        }

        $redirectToEdit = Route::_(
            sprintf(
                'index.php?option=%s&view=%s' . $this->getRedirectToItemAppend($recordId, $urlVar),
                $this->option,
                $this->view_item
            ),
            false
        );

        $data[$key] = $recordId;

        // The save2copy task needs to be handled slightly differently.
        if ($task == 'save2copy') {
            if ($checkin && $model->checkin($data[$key]) === false) {
                // Check-in failed. Go back to the item and display a notice.
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
                $this->setMessage($this->getError(), 'error');

                $this->setRedirect($redirectToEdit);

                return false;
            }

            // Reset the ID and then treat the request as for Apply.
            $data[$key] = 0;
            $task       = 'apply';
        }

        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect($redirectToList);

            return false;
        }

        if (!empty($data['custom'])) {
            $data['customfieldsdata'] = $model->toJSON($data['custom']);

            //remove the individual data from processing.
            unset($data['custom']);
        }

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        $validData = $model->validate($form, $data);
        if ($validData === false) {
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            $this->setRedirect($redirectToEdit);

            return false;
        }

        if (!$model->save($validData)) {
            $app->setUserState($context . '.data', $validData);

            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect($redirectToEdit);

            return false;
        }

        // Save succeeded, so check-in the record.
        if ($checkin && $model->checkin($validData[$key]) === false) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Check-in failed, so go back to the record and display a notice.
            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect($redirectToEdit);

            return false;
        }

        $this->setMessage(
            Text::_(
                ($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
                    ? $this->text_prefix
                    : 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
            )
        );

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task) {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);
                $model->checkout($recordId);

                $this->setRedirect($redirectToEdit);
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                $this->setRedirect($redirectToEdit);
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                $this->setRedirect($redirectToList);
                break;
        }

        $this->postSaveHook($model, $validData);

        return true;
    }
}
