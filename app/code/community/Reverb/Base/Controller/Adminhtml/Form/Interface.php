<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

interface Reverb_Base_Controller_Adminhtml_Form_Interface
{
    // The following methods are REQUIRED for all leaf classes which implement this interface
    /**
     * This method should inspect the data being posted and validate it. If it is valid, it should set
     *  the data on $objectToCreate
     *
     * @param $objectToCreate - New object being created
     * @param $posted_object_data
     * @return $objectToCreate - Should return the object which was passed in with data initialized appropriately.
     *                          If there is an error with the data, should throw exception of type Reverb_Base_Model_Adminhtml_Exception
     */
    public function validateDataAndCreateObject($objectToCreate, $posted_object_data);

    /**
     * This method should inspect the data being posted and validate it. If it is valid, it should set
     *  the data on $objectToUpdate
     *
     * @param $objectToUpdate - Existing object being updated
     * @param $posted_object_data
     * @return $objectToUpdate - Should return the object which was passed in with data updated appropriately.
     *                          If there is an error with the data, should throw exception of type Reverb_Base_Model_Adminhtml_Exception
     */
    public function validateDataAndUpdateObject($objectToUpdate, $posted_object_data);

    /**
     * Should be the name of whatever parameter will pass the object to be
     * created/edited/deleted's id to the controller
     *
     * @return string
     */
    public function getObjectParamName();

    /**
     * Should be a human readable description of what object this form is
     *  creating/editing/deleting. This will likely be the same value as
     *  Reverb_Base_Controller_Adminhtml_Interface::getModuleInstanceDescription()
     *
     * e.g. Product or Category
     *
     * @return string
     */
    public function getObjectDescription();

    /**
     * For whatever object is being edited/saved/deleted by this controller, this should be what comes
     * after the module's groupname in the object's full classname
     *  e.g.    catalog/product <--- would be "product" in this case
     *
     * @return string
     */
    public function getModuleInstance();

    /**
     * Should be the block name of whatever block is to be intialized to load the controller's
     * index action layout
     *
     * e.g.
     *  If the block to load the page's layout is "adminhtml/sales_order_edit" then
     *  this method should return "sales_order". IT SHOULD NOT CONTAIN THE "edit" and the
     *  block's class name MUST end in _Edit
     *
     * @return string
     */
    public function getFormBlockName();

    /**
     * Should be the controller that will process the form actions
     * e.g. if the url for the save action is {frontname}/{controller}/save
     *              then this method should return the {controller} value
     *
     * @return string
     */
    public function getFormActionsController();

    // OPTIONAL


    // The following methods are OPTIONAL for classes which inherit from Reverb_Base_Controller_Adminhtml_Form_Abstract

    /**
     * Should be the controller that will process the form actions
     * e.g. if the url for the save action is {frontname}/{controller}/{action}
     *
     *              then this method should return the {controller}/{action} value
     *
     * abstract class Reverb_Base_Controller_Adminhtml_Form_Abstract returns 'index/index' by default
     * Override this method if another uri should be used
     *
     * @return string
     */
    public function getFormBackControllerActionPath();
}