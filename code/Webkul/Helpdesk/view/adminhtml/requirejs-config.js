/**
 * Webkul_Helpdesk requirejs config
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
var config = {
    map: {
        '*': {
            events: 'Webkul_Helpdesk/js/events',
            options: 'Webkul_Helpdesk/js/options',
            customDependableTicketField: 'Webkul_Helpdesk/js/custom-dependable-ticket-field',
            hours: 'Webkul_Helpdesk/js/hours',
            holidays: 'Webkul_Helpdesk/js/holidays',
            responses: 'Webkul_Helpdesk/js/responses',
            condition: 'Webkul_Helpdesk/js/condition',
            viewreply: 'Webkul_Helpdesk/js/viewreply',
            merge: 'Webkul_Helpdesk/js/merge',
            dashboard: 'Webkul_Helpdesk/js/dashboard'
        }
    }
};
