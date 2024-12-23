<?php
// DisplayServiceController.php
require_once 'Model/DisplayServiceModel.php';

class DisplayServiceController
{
    protected $displayServiceModel;

    public function __construct()
    {
        $this->displayServiceModel = new DisplayServiceModel();
    }

    public function getServiceData()
    {
        $pendingAppointments = $this->displayServiceModel->getPendingAppointments();
        $onServiceAppointments = $this->displayServiceModel->getOnServiceAppointments();
        $completedAppointments = $this->displayServiceModel->getCompletedAppointments();

        return [
            'pending' => $pendingAppointments,
            'onService' => $onServiceAppointments,
            'completed' => $completedAppointments,
        ];
    }
}
?>
