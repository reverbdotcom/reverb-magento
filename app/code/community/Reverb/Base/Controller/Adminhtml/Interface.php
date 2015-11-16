<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

interface Reverb_Base_Controller_Adminhtml_Interface
{
    // The following methods are required. They will provide basic indexAction functionality
    /**
     * Should be the groupname of the module extending this class.
     *  e.g.
     *      <config>
     *          <global>
     *              <models|blocks|helpers>
     *                  <reverb_base>  <-- Should be this value
     *
     * This same value should also be used for the frontname of the routers used for
     *  this controller
     *
     * @return string
     */
    public function getModuleGroupname();

    /**
     * Should be the path to the menu item
     *  e.g. in adminhtml.xml
     * <?xml version="1.0"?>
     * <config>
    <menu>
    <path_node_1>
    <children>
    <path_node_2 translate="title" module="module groupname referenced above in getModuleGroupname">
    <children>
    <path_node_3>
     *
     * This value would be path_node_1/path_node_2/path_node_3
     *
     * @return string
     */
    public function getControllerActiveMenuPath();

    /**
     * Should be a human readable description of the controller's object/purpose
     * e.g. Product or Report
     *
     * @return string
     */
    public function getModuleInstanceDescription();

    /**
     * Should be the block name of whatever block is to be initialized to load the controller's
     * index action layout
     *
     * e.g.
     *  If the block to load the page's layout is "adminhtml/sales_order" then
     *  this method should return "sales_order"
     *
     * @return string
     */
    public function getIndexBlockName();

    /**
     * Should be the controller that will process the index actions
     * e.g. if the url for the index action is admin/{controller}/index
     *              then this method should return the {controller} value
     *
     *
     * @return string
     */
    public function getIndexActionsController();
}