<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webkul\Helpdesk\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class HelpdeskData implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
        \Webkul\Helpdesk\Model\BusinesshoursFactory $businesshoursFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\SupportCenterFactory $supportCenterFactory,
        \Webkul\Helpdesk\Model\AgentLevelFactory $agentLevelFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        DateTime $date
    ) {
        $this->agentLevelFactory = $agentLevelFactory;
        $this->groupFactory = $groupFactory;
        $this->businesshoursFactory = $businesshoursFactory;
        $this->eventsFactory = $eventsFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->ticketsPriorityFactory = $ticketsPriorityFactory;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->typeFactory = $typeFactory;
        $this->ticketsStatusFactory = $ticketsStatusFactory;
        $this->rulesFactory = $rulesFactory;
        $this->roleFactory = $roleFactory;
        $this->supportCenterFactory = $supportCenterFactory;
        $this->_date = $date;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addEntityType('ticketsystem_ticket', [
            'entity_model'          =>Webkul\Helpdesk\Model\Tickets::class,
            'attribute_model'       =>'',
            'table'         =>Webkul\Helpdesk\Model\Tickets::class,
            'increment_model'       =>'',
            'increment_per_store'   =>'0'
        ]);
        $ticketTypeData = [
            [
                'type_name' => 'Question',
                'description' => 'Question',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'type_name' => 'Pre-Sale Query',
                'description' => 'Pre-Sale Query',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'type_name' => 'Refund',
                'description' => 'Refund',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'type_name' => 'Support',
                'description' => 'Support',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'type_name' => 'Sales',
                'description' => 'Sales',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
        ];

        foreach ($ticketTypeData as $data) {
             $this->typeFactory->create()->setData($data)->save();
        }

        $ticketStatusData = [
            [
                'name' => 'Open',
                'description' => 'Open',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'New',
                'description' => 'New',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Pending',
                'description' => 'Pending',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Resolve',
                'description' => 'Resolve',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Close',
                'description' => 'Close',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Spam',
                'description' => 'Spam',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
        ];

        foreach ($ticketStatusData as $data) {
             $this->ticketsStatusFactory->create()->setData($data)->save();
        }

        $ticketPriorityData = [
            [
                'name' => 'High',
                'description' => 'High',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Medium',
                'description' => 'Medium',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Low',
                'description' => 'Low',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Urgent',
                'description' => 'Urgent',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
        ];

        foreach ($ticketPriorityData as $data) {
             $this->ticketsPriorityFactory->create()->setData($data)->save();
        }

        $eventData = [
            [
                'name' => 'Send Mail to Customer after adding reply to ticket',
                'description' => 'Send Mail to Customer after adding reply to ticket',
                'event' => '{"action-type":["note"],"note":{"added":"forward"}}',
                'one_condition_check' => '',
                'all_condition_check' => 'null',
                'actions' =>
                '{
                    "action-type":["mail_customer"],"mail_customer":{"template_id":"",
                        "subject":[" {{var agent_name}} replied your ticket {{var ticketid}}",
                        "content":["<p>{{var reply}}</p>"]}
                }',
                'status' => '1'
            ],
            [
                'name' => 'Send Mail to Agent after adding query to ticket',
                'description' => 'Send Mail to Agent after adding query to ticket',
                'event' => '{"action-type":["reply"],"reply":{"added":"customer"}}',
                'one_condition_check' => '',
                'all_condition_check' => '',
                'actions' =>
                '{
                    "action-type":["mail_agent"],"mail_agent":{"agent":["agent"],
                        "template_id":"","subject":["{{var customer_name}} asked on ticket {
                            {var ticketid}}"],"content":["<p>{{var reply}}</p>"]}
                 }',
                'status' => '1'
            ]
        ];

        foreach ($eventData as $data) {
             $this->eventsFactory->create()->setData($data)->save();
        }

        $businesshoursData = [
            [
                'businesshour_name' => 'Default Business Hour',
                'description' => 'Default Business Hour',
                'timezone' => 'America/Los_Angeles',
                'hours_type' => '0',
                'helpdesk_hours' =>
                '{"Monday":{"morning_Monday":"9:00","morning_cycle_Monday":"am", 
                    "evening_Monday":"5:00","evening_cycle_Monday":"pm"},"Tuesday":
                    {"morning_Tuesday":"8:00","morning_cycle_Tuesday":"am",
                        "evening_Tuesday":"5:00","evening_cycle_Tuesday":"pm"},"Wednesday":
                        {"morning_Wednesday":"8:00","morning_cycle_Wednesday":"am",
                            "evening_Wednesday":"5:00","evening_cycle_Wednesday":"pm"},
                            "Thursday":{"morning_Thursday":"8:00",
                                "morning_cycle_Thursday":"am","evening_Thursday":"5:00",
                                "evening_cycle_Thursday":"pm"},"Friday":
                                {"morning_Friday":"9:00","morning_cycle_Friday":"am",
                                    "evening_Friday":"6:00","evening_cycle_Friday":"pm"}}',
                'hollyday_list' => '[]'
            ]
        ];

        foreach ($businesshoursData as $data) {
             $this->businesshoursFactory->create()->setData($data)->save();
        }

        $goupData = [
            [
                'group_name' => 'Product Management',
                'agent_ids' => '',
                'businesshour_id' => 1,
                'created_at' => $this->_date->gmtDate(),
                'is_active' => 1
            ],
            [
                'group_name' => 'QA',
                'agent_ids' => '',
                'businesshour_id' => 1,
                'created_at' => $this->_date->gmtDate(),
                'is_active' => 1
            ],
            [
                'group_name' => 'Sales',
                'agent_ids' => '',
                'businesshour_id' => 1,
                'created_at' => $this->_date->gmtDate(),
                'is_active' => 1
            ]
        ];

        foreach ($goupData as $data) {
            $this->groupFactory->create()->setData($data)->save();
        }

        $supportCenterData = [
            [
                'name' => 'Error / Bug',
                'description' =>
                'Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
                Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,
                when an unknown printer took a galley of type and scrambled it to make a type 
                specimen book. It has survived not only five centuries, but also the leap 
                into electronic typesetting, remaining essentially unchanged. It was 
                popularised in the 1960s with the release of Letraset sheets containing Lorem 
                Ipsum passages, and more recently with desktop publishing software like Aldus 
                PageMaker including versions of Lorem Ipsum.Test Support Item',
                'cms_id' => 'about-magento-demo-store,customer-service',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ]
        ];

        foreach ($supportCenterData as $data) {
             $this->supportCenterFactory->create()->setData($data)->save();
        }

        $agentLevelData = [
            [
                'name' => 'Expert',
                'description' => 'Agent has Expert level of knowledge.',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'High',
                'description' => 'Agent has High level of knowledge.',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ],
            [
                'name' => 'Low',
                'description' => 'Agent has a good level of skills.',
                'created_at' => $this->_date->gmtDate(),
                'status' => 1
            ]
        ];

        foreach ($agentLevelData as $data) {
             $this->agentLevelFactory->create()->setData($data)->save();
        }

        $role=$this->roleFactory->create();
        $role->setName('Supervisor')
                ->setPid(0)
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
        $role->save();

        $resource=['Magento_Backend::admin',
                    'Webkul_Helpdesk::manager',
                    'Webkul_Helpdesk::dashboard',
                    'Webkul_Helpdesk::ticketsmanagement',
                    'Webkul_Helpdesk::tickets',
                    'Webkul_Helpdesk::edit_status',
                    'Webkul_Helpdesk::send_reply',
                    'Webkul_Helpdesk::add_note',
                    'Webkul_Helpdesk::add_cc_bcc',
                    'Webkul_Helpdesk::assign_ticket',
                    'Webkul_Helpdesk::edit_ticket_properties',
                    'Webkul_Helpdesk::forward_ticket',
                    'Webkul_Helpdesk::type',
                    'Webkul_Helpdesk::status',
                    'Webkul_Helpdesk::priority',
                    'Webkul_Helpdesk::customattribute',
                    'Webkul_Helpdesk::agentsmanagement',
                    'Webkul_Helpdesk::agent',
                    'Webkul_Helpdesk::customer',
                    'Webkul_Helpdesk::customerorganization',
                    'Webkul_Helpdesk::businesshours',
                    'Webkul_Helpdesk::events',
                    'Webkul_Helpdesk::responses',
                    'Webkul_Helpdesk::tag',
                    'Webkul_Helpdesk::supportcenter',
                    'Webkul_Helpdesk::connectemail',
                    'Webkul_Helpdesk::activity',
                    'Webkul_Helpdesk::reporting',
                    'Webkul_Helpdesk::reportcustomer',
                    'Webkul_Helpdesk::reportagent',
                    'Webkul_Helpdesk::reporting'
                  ];

        $this->rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

        $role=$this->roleFactory->create();
        $role->setName('Agent')
            ->setPid(0) //set parent role id of your role
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
        $role->save();

        $resource=['Magento_Backend::admin',
                    'Webkul_Helpdesk::manager',
                    'Webkul_Helpdesk::dashboard',
                    'Webkul_Helpdesk::ticketsmanagement',
                    'Webkul_Helpdesk::tickets',
                    'Webkul_Helpdesk::edit_status',
                    'Webkul_Helpdesk::send_reply',
                    'Webkul_Helpdesk::add_note',
                    'Webkul_Helpdesk::add_cc_bcc',
                    'Webkul_Helpdesk::type',
                    'Webkul_Helpdesk::status',
                    'Webkul_Helpdesk::priority',
                    'Webkul_Helpdesk::customattribute',
                    'Webkul_Helpdesk::customer',
                    'Webkul_Helpdesk::customerorganization',
                    'Webkul_Helpdesk::businesshours',
                    'Webkul_Helpdesk::events',
                    'Webkul_Helpdesk::responses',
                    'Webkul_Helpdesk::tag',
                    'Webkul_Helpdesk::supportcenter',
                    'Webkul_Helpdesk::connectemail',
                    'Webkul_Helpdesk::activity',
                    'Webkul_Helpdesk::reporting',
                    'Webkul_Helpdesk::reportcustomer',
                    'Webkul_Helpdesk::reportagent',
                    'Webkul_Helpdesk::reporting'
                  ];

        $this->rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
