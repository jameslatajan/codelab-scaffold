<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GenericModel;
use App\Models\LogModel;
use App\Models\Userrole_model;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Activity_designs extends BaseController
{
    protected $roles;
    protected $table;
    protected $pfield;
    protected $sub;
    protected $modules;
    protected $module;
    protected $module_path;
    protected $controller_page;
    protected $dir;
    protected $status_map;
    protected $participation_map;
    protected $nomination_type_map;

    public function __construct()
    {
        $this->main = 'Trainings';
        $this->module = 'Trainings';
        $this->sub = 'Activity Design';

        $this->data['table'] = $this->table = PREFIX . 'activity_designs';
        $this->data['pfield'] = $this->pfield = 'id';
        $this->data['controller_page'] = $this->controller_page = site_url('activity_designs');
        $this->module_path = 'modules/' . strtolower(str_replace(' ', '_', $this->main)) . '/' . strtolower(str_replace(' ', '_', $this->sub));

        $this->data['status_map'] = $this->status_map = [
            -1 => ['text' => 'Declined', 'color' => 'danger'],
            0 => ['text' => 'Cancelled', 'color' => 'danger'],
            1 => ['text' => 'Pending', 'color' => 'secondary'],
            2 => ['text' => 'Reviewed', 'color' => 'warning'],
            3 => ['text' => 'Approved', 'color' => 'success'],
        ];

        $this->data['participation_map'] = $this->participation_map = [
            1 => ['text' => 'Multiple Divisions', 'color' => ''],
            2 => ['text' => 'Multiple Sections', 'color' => ''],
            3 => ['text' => 'Section Employees', 'color' => ''],
            4 => ['text' => 'Individual Employees', 'color' => ''],
        ];

        $this->data['nomination_type_map'] = $this->nomination_type_map = [
            1 => ['text' => 'OB', 'color' => 'primary'],
            2 => ['text' => 'OTR', 'color' => 'info'],
            3 => ['text' => 'OT', 'color' => 'success'],
        ];

        $this->data['type_map'] = $this->nomination_type_map;
        $this->data['dir'] = $this->dir = 'uploads/activity_design/';
    }

    private function submenu()
    {
        foreach (MODULES as $mod) {
            require '../app/Views/modules/' . str_replace(' ', '_', strtolower($mod)) . '/metadata.php';
        }

        $this->data['modules'] = $this->modules;
        $this->data['current_main_module'] = $this->modules[$this->main]['main'];
        $this->data['current_module'] = $this->modules[$this->main]['sub'][$this->sub];

        $this->check_roles();
        $this->data['roles'] = $this->roles;
    }

    private function check_roles()
    {
        $userRoleModel = new Userrole_model();
        $user_id = session()->get('current_user')->userID;

        $this->roles['view'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' View ' . $this->sub);
        $this->roles['create'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Create ' . $this->sub);
        $this->roles['edit'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Edit ' . $this->sub);
        $this->roles['export'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Export ' . $this->sub);
        $this->roles['confirm'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Confirm ' . $this->sub);
        $this->roles['verify'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Verify ' . $this->sub);
        $this->roles['review'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Review ' . $this->sub);
        $this->roles['approve'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Approve ' . $this->sub);
        $this->roles['decline'] = $userRoleModel->has_access($user_id, SYSTEM_NAME . ' Decline ' . $this->sub);
    }

    public function index()
    {
        $this->show();
    }

    private function get_conditions()
    {
        return [
            [
                'variable' => 'dateCreated',
                'field' => $this->table . '.dateCreated',
                'default_value' => '',
                'operator' => 'date',
                'title' => 'Created',
                'width' => '100',
                'excel_width' => '20',
                'align' => 'left',
                'class' => 'wx-150',
            ],
            [
                'variable' => 'nominationNo',
                'field' => $this->table . '.nominationNo',
                'default_value' => '',
                'operator' => 'like_after',
                'title' => 'Nomination No.',
                'width' => '110',
                'excel_width' => '20',
                'align' => 'left',
                'class' => 'wx-150',
            ],
            [
                'variable' => 'activity',
                'field' => 'petru_activities.activity',
                'default_value' => '',
                'operator' => 'like_after',
                'title' => 'Activity',
                'width' => '100',
                'excel_width' => '40',
                'align' => 'left',
                'class' => 'wx-300',
            ],
            [
                'variable' => 'venue',
                'field' => 'petru_activities.venue',
                'default_value' => '',
                'operator' => 'like_after',
                'title' => 'Venue',
                'width' => '100',
                'excel_width' => '30',
                'align' => 'left',
                'class' => 'wx-200',
            ],
            [
                'variable' => 'budget',
                'field' => '',
                'default_value' => '',
                'operator' => '',
                'title' => 'Budget',
                'width' => '80',
                'excel_width' => '16',
                'align' => 'right',
                'class' => 'wx-120 text-end',
            ],
            [
                'variable' => 'participantCount',
                'field' => '',
                'default_value' => '',
                'operator' => '',
                'title' => 'Participants',
                'width' => '70',
                'excel_width' => '15',
                'align' => 'center',
                'class' => 'wx-100 text-center',
            ],
            [
                'variable' => 'nominationType',
                'field' => PREFIX . 'nomination_details.type',
                'default_value' => '',
                'operator' => 'where',
                'title' => 'Category',
                'width' => '70',
                'excel_width' => '15',
                'align' => 'center',
                'class' => 'wx-100 text-center',
            ],
            [
                'variable' => 'status',
                'field' => $this->table . '.status',
                'default_value' => '',
                'operator' => 'where',
                'title' => 'Status',
                'width' => '70',
                'excel_width' => '15',
                'align' => 'center',
                'class' => 'wx-120 text-center',
            ],
        ];
    }

    private function get_filter_values($controller)
    {
        $condition_fields = $this->get_conditions();
        $filter_source = 0;

        if ($this->request->getPost('filterflag') || $this->request->getPost('sortby')) {
            $filter_source = 1;
        } else {
            foreach ($condition_fields as $key) {
                if ($this->request->getPost($key['variable'])) {
                    $filter_source = 1;
                    break;
                }
            }
        }

        if (!$filter_source) {
            foreach ($condition_fields as $key) {
                if (
                    $this->session->get($controller . '_' . $key['variable']) ||
                    $this->session->get($controller . '_sortby') ||
                    $this->session->get($controller . '_sortorder')
                ) {
                    $filter_source = 2;
                    break;
                }
            }
        }

        $filters = [];
        if ($filter_source === 1) {
            foreach ($condition_fields as $key) {
                $filters[$key['variable']] = trim((string) $this->request->getPost($key['variable']));
            }

            $sortby = (string) $this->request->getPost('sortby');
            $sortorder = (string) $this->request->getPost('sortorder');
        } elseif ($filter_source === 2) {
            foreach ($condition_fields as $key) {
                $filters[$key['variable']] = (string) $this->session->get($controller . '_' . $key['variable']);
            }

            $sortby = (string) $this->session->get($controller . '_sortby');
            $sortorder = (string) $this->session->get($controller . '_sortorder');
        } else {
            foreach ($condition_fields as $key) {
                $filters[$key['variable']] = $key['default_value'];
            }

            $sortby = '';
            $sortorder = '';
        }

        return [$filters, $sortby, $sortorder];
    }

    private function base_list_builder()
    {
        $b = $this->db->table($this->table);
        $b->select($this->table . '.' . $this->pfield);
        $b->select($this->table . '.actID');
        $b->select($this->table . '.nominationNo');
        $b->select($this->table . '.dateCreated');
        $b->select($this->table . '.status');
        $b->select('petru_activities.activityNo');
        $b->select('petru_activities.activity');
        $b->select('petru_activities.date');
        $b->select('petru_activities.venue');
        $b->select('petru_activities.budget');
        $b->select('COUNT(' . PREFIX . 'nomination_details.nomDetID) AS participantCount', false);
        $b->select('MIN(' . PREFIX . 'nomination_details.type) AS nominationType', false);
        $b->join('petru_activities', $this->table . '.actID = petru_activities.actID', 'left');
        $b->join(PREFIX . 'nomination_details', PREFIX . 'nomination_details.nomID = ' . $this->table . '.nomID', 'left');
        $b->groupBy([
            $this->table . '.nomID',
            $this->table . '.actID',
            $this->table . '.nominationNo',
            $this->table . '.dateCreated',
            $this->table . '.status',
            'petru_activities.activityNo',
            'petru_activities.activity',
            'petru_activities.date',
            'petru_activities.venue',
            'petru_activities.budget',
        ]);

        return $b;
    }

    private function apply_list_filters($b, array $filters)
    {
        if ($filters['dateCreated'] !== '') {
            $date_created = date('Y-m-d', strtotime($filters['dateCreated']));
            $b->where('DATE(' . $this->table . '.dateCreated)', $date_created, false);
        }

        if ($filters['nominationNo'] !== '') {
            $b->like($this->table . '.nominationNo', $filters['nominationNo'], 'after');
        }

        if ($filters['activity'] !== '') {
            $b->like('petru_activities.activity', $filters['activity'], 'after');
        }

        if ($filters['venue'] !== '') {
            $b->like('petru_activities.venue', $filters['venue'], 'after');
        }

        if ($filters['nominationType'] !== '') {
            $b->where(PREFIX . 'nomination_details.type', (int) $filters['nominationType']);
        }

        if ($filters['status'] !== '') {
            $b->where($this->table . '.status', (int) $filters['status']);
        }
    }

    private function get_filtered_count(array $filters)
    {
        $b = $this->db->table($this->table);
        $b->select('COUNT(DISTINCT ' . $this->table . '.nomID) AS ttl', false);
        $b->join('petru_activities', $this->table . '.actID = petru_activities.actID', 'left');
        $b->join(PREFIX . 'nomination_details', PREFIX . 'nomination_details.nomID = ' . $this->table . '.nomID', 'left');
        $this->apply_list_filters($b, $filters);

        $row = $b->get()->getRow();

        return $row ? (int) $row->ttl : 0;
    }

    private function build_list_data($list)
    {
        $data = [];

        foreach ($list as $row) {
            $id = $this->encrypter->encode($row->{$this->pfield});
            $category = $this->nomination_type_map[(int) $row->nominationType]['text'] ?? '-';
            $status = $this->status_map[(int) $row->status] ?? ['text' => 'Unknown', 'color' => 'secondary'];

            $data[$id] = [
                [
                    'text' => $row->dateCreated ? date('M d, Y h:i A', strtotime($row->dateCreated)) : '-',
                    'class' => '',
                    'align' => 'left',
                ],
                [
                    'text' => $row->nominationNo ?: '-',
                    'class' => '',
                    'align' => 'left',
                ],
                [
                    'text' => $row->activity ?: '-',
                    'class' => '',
                    'align' => 'left',
                ],
                [
                    'text' => $row->venue ?: '-',
                    'class' => '',
                    'align' => 'left',
                ],
                [
                    'text' => is_numeric($row->budget) ? number_format((float) $row->budget, 2) : '-',
                    'class' => 'text-end',
                    'align' => 'right',
                ],
                [
                    'text' => (int) $row->participantCount,
                    'class' => 'text-center',
                    'align' => 'center',
                ],
                [
                    'text' => $category,
                    'class' => 'text-center',
                    'align' => 'center',
                ],
                [
                    'text' => '<span class="text-white text-center rounded-pill text-uppercase bg-' . $status['color'] . '">' . $status['text'] . '</span>',
                    'class' => 'text-center',
                    'align' => 'center',
                ],
            ];
        }

        return $data;
    }

    public function show()
    {
        $this->submenu();
        $data = $this->data;
        $condition_fields = $this->get_conditions();
        $sorting_fields = [
            'dateCreated' => 'desc',
            'id' => 'desc',
        ];
        $controller = strtolower(str_replace(' ', '_', $this->module)) . '_' . strtolower(str_replace(' ', '_', $this->sub));
        $page = $this->request->getVar('page') ?: ($this->session->get($controller . '_page') ?: 1);

        [$filters, $sortby, $sortorder] = $this->get_filter_values($controller);

        if ($this->request->getPost('limit')) {
            if ($this->request->getPost('limit') === 'All') {
                $limit = $this->get_filtered_count($filters);
            } else {
                $limit = (int) $this->request->getPost('limit');
            }
        } elseif ($this->session->get($controller . '_limit')) {
            $limit = (int) $this->session->get($controller . '_limit');
        } else {
            $limit = 25;
        }

        foreach ($condition_fields as $key) {
            $this->session->set($controller . '_' . $key['variable'], $filters[$key['variable']]);
            $data[$key['variable']] = $filters[$key['variable']];
        }

        $this->session->set($controller . '_sortby', $sortby);
        $this->session->set($controller . '_sortorder', $sortorder);
        $this->session->set($controller . '_limit', $limit);
        $this->session->set($controller . '_page', $page);

        $ttl_rows = $this->get_filtered_count($filters);
        $data['ttl_rows'] = $ttl_rows;
        $data['pagination'] = $this->pagination->makeLinks($page, $limit, $ttl_rows, 'custom');

        $b = $this->base_list_builder();
        $this->apply_list_filters($b, $filters);

        if ($sortby && $sortorder) {
            $b->orderBy($sortby, $sortorder);
            foreach ($sorting_fields as $field => $default_order) {
                if ($field !== $sortby) {
                    $b->orderBy($field, $default_order);
                }
            }
        } else {
            foreach ($sorting_fields as $field => $default_order) {
                if ($sortby === '') {
                    $sortby = $field;
                    $sortorder = $default_order;
                }

                $b->orderBy($field, $default_order);
            }
        }

        $offset = 0;
        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $b->limit($limit, $offset);
        }

        $records = $b->get()->getResult();

        $data['sortby'] = $sortby;
        $data['sortorder'] = $sortorder;
        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['buttons'] = [
            [
                'class' => 'btn btn-light btn-rounded text-primary btn-sm',
                'tooltip' => 'Print',
                'icon' => '<i class="fas fa-print"></i>',
                'onclickUrl' => $this->controller_page . '/printlist',
            ],
            [
                'class' => 'btn btn-light text-primary btn-rounded btn-sm',
                'tooltip' => 'Export',
                'icon' => '<i class="fas fa-file-excel"></i>',
                'onclickUrl' => $this->controller_page . '/exportlist',
            ],
        ];
        $data['action_title'] = 'List';
        $data['headers'] = generate_headers($condition_fields);
        $data['records'] = $this->build_list_data($records);

        echo view('header', $data);
        echo view($this->module_path . '/show');
        echo view('footer');
    }

    private function get_approved_activity_ids($exclude_id = null)
    {
        $b = $this->db->table($this->table);
        $b->select('actID');
        $b->where('status', 3);

        if ($exclude_id) {
            $b->where('nomID !=', $exclude_id);
        }

        return array_column($b->get()->getResultArray(), 'actID');
    }

    private function get_active_activities($exclude_id = null)
    {
        $invalid_act_ids = $this->get_approved_activity_ids($exclude_id);

        $b = $this->db->table(PREFIX . 'activities');
        $b->select(PREFIX . 'activities.*');
        $b->where('status', 5);

        if (!empty($invalid_act_ids)) {
            $b->whereNotIn('actID', $invalid_act_ids);
        }

        $b->orderBy('activity', 'asc');

        return $b->get()->getResult();
    }

    private function get_divisions()
    {
        $b = $this->db->table('divisions');
        $b->select('divisions.*');
        $b->where('status', 1);
        $b->orderBy('divisionAbbr', 'asc');

        return $b->get()->getResult();
    }

    private function get_sections()
    {
        $b = $this->db->table('sections');
        $b->select('sections.*');
        $b->where('status', 1);
        $b->orderBy('secAbbr', 'asc');

        return $b->get()->getResult();
    }

    private function get_employee_types()
    {
        $b = $this->db->table('hris_employee_types');
        $b->select('hris_employee_types.*');
        $b->where('status', 1);
        $b->orderBy('employeeType', 'asc');

        return $b->get()->getResult();
    }

    public function create()
    {
        $this->submenu();
        $data = $this->data;

        if ($this->roles['create']) {
            $data['activities'] = $this->get_active_activities();
            $data['divisions'] = $this->get_divisions();
            $data['sections'] = $this->get_sections();
            $data['empTypes'] = $this->get_employee_types();

            echo view('header', $data);
            echo view($this->module_path . '/create');
            echo view('footer');
            return;
        }

        $data['page_title'] = 'Unauthorized';
        $data['page_desc'] = 'You dont have access to this page';
        $data['url'] = $this->controller_page;

        echo view('header', $data);
        echo view('default');
        echo view('footer');
    }

    public function create_batch()
    {
        return redirect()->to($this->controller_page . '/create');
    }

    private function get_nomination_record($id)
    {
        $b = $this->db->table($this->table);
        $b->select($this->table . '.' . $this->pfield);
        $b->select($this->table . '.*');
        $b->select('petru_activities.activityNo');
        $b->select('petru_activities.activity');
        $b->select('petru_activities.type AS activityType');
        $b->select('petru_activities.date');
        $b->select('petru_activities.venue');
        $b->select('petru_activities.budget');
        $b->select('petru_activities.files');
        $b->join('petru_activities', $this->table . '.actID = petru_activities.actID', 'left');

        foreach (['created', 'reviewed', 'approved', 'declined', 'cancelled'] as $alias) {
            $column = $alias . 'By';
            $user_alias = $alias . '_user';
            $b->select($user_alias . '.firstName AS ' . $alias . '_firstname');
            $b->select($user_alias . '.middleName AS ' . $alias . '_middlename');
            $b->select($user_alias . '.lastName AS ' . $alias . '_lastname');
            $b->join('users AS ' . $user_alias, $this->table . '.' . $column . ' = ' . $user_alias . '.userID', 'left');
        }

        $b->where($this->table . '.' . $this->pfield, $id);
        $rec = $b->get()->getRow();

        if (!$rec) {
            return null;
        }

        $d = $this->db->table(PREFIX . 'nomination_details');
        $d->select('COUNT(nomDetID) AS participantCount', false);
        $d->select('MIN(type) AS nominationType', false);
        $d->where('id', $id);
        $detail = $d->get()->getRow();

        $rec->participantCount = $detail ? (int) $detail->participantCount : 0;
        $rec->nominationType = $detail ? (int) $detail->nominationType : 0;

        return $rec;
    }

    private function get_nomination_participants($nom_id, $for_edit = false)
    {
        $participants = [];

        $b = $this->db->table(PREFIX . 'nomination_details');
        $b->select(PREFIX . 'nomination_details.*');
        $b->select('hris_employees.empNo');
        $b->select('hris_employees.fname');
        $b->select('hris_employees.mname');
        $b->select('hris_employees.lname');
        $b->select('units.unitName');
        $b->select('sections.sectionName');
        $b->select('divisions.divisionName');
        $b->select('hris_employee_types.employeeType');
        $b->select('hris_job_titles.jobTitle');
        $b->join('hris_employees', PREFIX . 'nomination_details.empID = hris_employees.empID', 'left');
        $b->join('hris_employments', PREFIX . 'nomination_details.empID = hris_employments.empID', 'left');
        $b->join('hris_job_titles', 'hris_employments.jobTitleID = hris_job_titles.jobTitleID', 'left');
        $b->join('hris_employee_types', 'hris_employments.employeeTypeID = hris_employee_types.employeeTypeID', 'left');
        $b->join('units', 'hris_employments.unitID = units.unitID', 'left');
        $b->join('sections', 'hris_employments.secID = sections.secID', 'left');
        $b->join('divisions', 'hris_employments.divisionID = divisions.divisionID', 'left');
        $b->where(PREFIX . 'nomination_details.nomID', $nom_id);
        $b->orderBy('units.unitName', 'asc');
        $b->orderBy('hris_employees.lname', 'asc');
        $b->orderBy('hris_employees.fname', 'asc');

        foreach ($b->get()->getResult() as $row) {
            $full_name = trim($row->lname . ', ' . $row->fname . ' ' . ($row->mname ?? ''));

            if ($for_edit) {
                $participants[] = [
                    'id' => $this->encrypter->encode($row->empID),
                    'text' => $row->empNo . ' - ' . $full_name,
                ];
                continue;
            }

            $participants[] = [
                'idNo' => $row->empNo,
                'name' => $full_name,
                'unitName' => $row->unitName,
                'sectionName' => $row->sectionName,
                'divisionName' => $row->divisionName,
                'employeeType' => $row->employeeType,
                'jobTitle' => $row->jobTitle,
                'type' => $row->type,
            ];
        }

        return $participants;
    }

    private function build_signatories($rec)
    {
        $config = [
            ['no' => 1, 'label' => 'Created By', 'key' => 'created', 'column' => 'createdBy', 'date' => 'dateCreated', 'theme' => 'done', 'always' => true],
            ['no' => 2, 'label' => 'Reviewed By', 'key' => 'reviewed', 'column' => 'reviewedBy', 'date' => 'dateReviewed', 'theme' => 'done', 'always' => true],
            ['no' => 3, 'label' => 'Approved By', 'key' => 'approved', 'column' => 'approvedBy', 'date' => 'dateApproved', 'theme' => 'done', 'always' => true],
            ['no' => '<i class="fas fa-times"></i>', 'label' => 'Cancelled By', 'key' => 'cancelled', 'column' => 'cancelledBy', 'date' => 'dateCancelled', 'theme' => 'danger', 'always' => false],
            ['no' => '<i class="fas fa-times"></i>', 'label' => 'Declined By', 'key' => 'declined', 'column' => 'declinedBy', 'date' => 'dateDeclined', 'theme' => 'danger', 'always' => false],
        ];

        $signatories = [];

        foreach ($config as $item) {
            $col = $item['column'];
            $show = $item['always'] || !empty($rec->{$col});
            $value = '-';

            if (!empty($rec->{$col})) {
                $lname = $item['key'] . '_lastname';
                $fname = $item['key'] . '_firstname';
                $value = strtoupper(trim(($rec->{$lname} ?? '') . ', ' . ($rec->{$fname} ?? '')));
            }

            $signatories[] = [
                'no' => $item['no'],
                'show' => $show,
                'label' => $item['label'],
                'value' => $value,
                'subTitle' => !empty($rec->{$item['date']}) ? date('F d, Y h:i A', strtotime($rec->{$item['date']})) : '-',
                'theme' => !empty($rec->{$col}) ? $item['theme'] : '',
            ];
        }

        return $signatories;
    }

    public function view($id)
    {
        $this->submenu();
        $data = $this->data;
        $id = (int) $this->encrypter->decode($id);

        if (!$this->roles['view']) {
            $data['page_title'] = 'Unauthorized';
            $data['page_desc'] = 'You dont have access to this page';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $rec = $this->get_nomination_record($id);
        if (!$rec) {
            $data['page_title'] = 'Not Found';
            $data['page_desc'] = 'Record was not found';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $data['rec'] = $rec;
        $data['participants'] = $this->get_nomination_participants($rec->nomID);
        $data['signatories'] = $this->build_signatories($rec);
        $data['logUrl'] = site_url('logs/single_record_log/' . $this->table . '/' . $this->pfield . '/' . $this->encrypter->encode($rec->{$this->pfield}) . '/' . $this->sub);

        echo view('header', $data);
        echo view($this->module_path . '/view');
        echo view('footer');
    }

    public function edit($id)
    {
        $this->submenu();
        $data = $this->data;
        $id = (int) $this->encrypter->decode($id);

        if (!$this->roles['edit']) {
            $data['page_title'] = 'Unauthorized';
            $data['page_desc'] = 'You dont have access to this page';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $rec = $this->get_nomination_record($id);

        if (!$rec || (int) $rec->status !== 1) {
            $data['page_title'] = 'Not Found';
            $data['page_desc'] = 'Only pending nominations can be edited';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $data['activities'] = $this->get_active_activities($id);
        $data['divisions'] = $this->get_divisions();
        $data['sections'] = $this->get_sections();
        $data['empTypes'] = $this->get_employee_types();
        $data['rec'] = $rec;
        $data['participants'] = $this->get_nomination_participants($rec->nomID, true);
        $data['logUrl'] = site_url('logs/single_record_log/' . $this->table . '/' . $this->pfield . '/' . $this->encrypter->encode($rec->{$this->pfield}) . '/' . $this->sub);

        echo view('header', $data);
        echo view($this->module_path . '/edit');
        echo view('footer');
    }

    public function get_section_employees()
    {
        $this->submenu();

        if (!$this->roles['create']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Get Employees',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $sec_id = (int) $this->encrypter->decode((string) $this->request->getGet('secID'));
        if ($sec_id <= 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Get Employees',
                'message' => 'Invalid section',
                'data' => [],
            ]);
        }

        $b = $this->db->table('hris_employments');
        $b->select('hris_employments.empID');
        $b->select('hris_employees.empNo');
        $b->select('hris_employees.fname');
        $b->select('hris_employees.mname');
        $b->select('hris_employees.lname');
        $b->join('hris_employees', 'hris_employments.empID = hris_employees.empID', 'left');
        $b->where('hris_employments.secID', $sec_id);
        $b->where('hris_employments.status', 1);
        $b->where('hris_employees.status', 1);
        $b->orderBy('hris_employees.lname', 'asc');
        $b->orderBy('hris_employees.fname', 'asc');

        $data = [];
        foreach ($b->get()->getResult() as $row) {
            $full_name = trim($row->lname . ', ' . $row->fname . ' ' . ($row->mname ?? ''));
            $data[] = [
                'id' => $this->encrypter->encode($row->empID),
                'idNo' => $row->empNo,
                'fullName' => $full_name,
            ];
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'title' => 'Employees',
            'message' => 'Employees loaded',
            'data' => $data,
        ]);
    }

    private function decode_id_list(array $values)
    {
        $decoded = [];

        foreach ($values as $value) {
            $id = (int) $this->encrypter->decode((string) $value);
            if ($id > 0) {
                $decoded[] = $id;
            }
        }

        return array_values(array_unique($decoded));
    }

    private function sanitize_employee_type_ids(array $values)
    {
        $types = [];

        foreach ($values as $value) {
            $type_id = (int) $value;
            if ($type_id > 0) {
                $types[] = $type_id;
            }
        }

        return array_values(array_unique($types));
    }

    private function collect_employee_ids($participant_source, array $divisions, array $sections, array $employees, array $employee_types)
    {
        $emp_ids = [];

        if ((int) $participant_source === 1) {
            $division_ids = $this->decode_id_list($divisions);
            if (!empty($division_ids)) {
                $b = $this->db->table('hris_employments');
                $b->select('empID');
                $b->whereIn('divisionID', $division_ids);
                $b->where('status', 1);

                if (!empty($employee_types)) {
                    $b->whereIn('employeeTypeID', $employee_types);
                }

                foreach ($b->get()->getResult() as $row) {
                    $emp_ids[] = (int) $row->empID;
                }
            }
        } elseif ((int) $participant_source === 2) {
            $section_ids = $this->decode_id_list($sections);
            if (!empty($section_ids)) {
                $b = $this->db->table('hris_employments');
                $b->select('empID');
                $b->whereIn('secID', $section_ids);
                $b->where('status', 1);

                if (!empty($employee_types)) {
                    $b->whereIn('employeeTypeID', $employee_types);
                }

                foreach ($b->get()->getResult() as $row) {
                    $emp_ids[] = (int) $row->empID;
                }
            }
        } else {
            $emp_ids = $this->decode_id_list($employees);
        }

        return array_values(array_unique($emp_ids));
    }

    private function validate_nomination_payload($id = null)
    {
        $enc_act_id = (string) $this->request->getPost('activityID');
        $participant_source = (int) $this->request->getPost('participantType');
        $nomination_type = (int) $this->request->getPost('nominationType');
        $divisions = (array) $this->request->getPost('divisions');
        $sections = (array) $this->request->getPost('sections');
        $employees = (array) $this->request->getPost('employees');
        $employee_types = $this->sanitize_employee_type_ids((array) $this->request->getPost('employeeTypes'));
        $act_id = $enc_act_id !== '' ? (int) $this->encrypter->decode($enc_act_id) : 0;

        if ($act_id <= 0) {
            return [false, 'Please select an activity'];
        }

        if (!isset($this->nomination_type_map[$nomination_type])) {
            return [false, 'Please select a valid nomination category'];
        }

        if (!in_array($participant_source, [1, 2, 3, 4], true)) {
            return [false, 'Invalid participant type'];
        }

        if ($participant_source === 1 && count($divisions) === 0) {
            return [false, 'Select at least one division'];
        }

        if ($participant_source === 2 && count($sections) === 0) {
            return [false, 'Select at least one section'];
        }

        if (in_array($participant_source, [3, 4], true) && count($employees) === 0) {
            return [false, 'Add at least one employee'];
        }

        $b = $this->db->table($this->table);
        $b->select('id');
        $b->where('actID', $act_id);
        $b->where('status', 3);

        if ($id) {
            $b->where('nomID !=', $id);
        }

        if ($b->get()->getRow()) {
            return [false, 'This activity is already approved in another nomination'];
        }

        $emp_ids = $this->collect_employee_ids($participant_source, $divisions, $sections, $employees, $employee_types);
        if (empty($emp_ids)) {
            return [false, 'No employees matched the selected participants'];
        }

        return [true, '', [
            'actID' => $act_id,
            'nominationType' => $nomination_type,
            'participantType' => $participant_source,
            'empIDs' => $emp_ids,
        ]];
    }

    public function save()
    {
        $this->submenu();
        $genModel = new GenericModel();
        $logModel = new LogModel();

        if (!$this->roles['create']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        [$valid, $message, $payload] = $this->validate_nomination_payload();
        if (!$valid) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => $message,
                'data' => [],
            ]);
        }

        $this->db->transStart();

        $header = [
            'nominationNo' => $genModel->gen_seriesNo($this->table, 'nominationNo', 'NN'),
            'actID' => $payload['actID'],
            'status' => 1,
            'dateCreated' => date('Y-m-d H:i:s'),
            'createdBy' => $this->current_user->userID,
        ];

        $b = $this->db->table($this->table);
        if (!$b->insert($header)) {
            $this->db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'title' => 'Error',
                'message' => $this->db->error()['message'],
                'data' => [],
            ]);
        }

        $id = $this->db->insertID();
        $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Insert', 'Record - ' . $this->sub);

        $detail_table = $this->db->table(PREFIX . 'nomination_details');
        foreach ($payload['empIDs'] as $emp_id) {
            if (!$detail_table->insert([
                'id' => $id,
                'empID' => $emp_id,
                'type' => $payload['nominationType'],
                'status' => 1,
            ])) {
                $this->db->transRollback();

                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 500,
                    'title' => 'Error',
                    'message' => $this->db->error()['message'],
                    'data' => [],
                ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'title' => 'Error',
                'message' => $this->db->error()['message'],
                'data' => [],
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'title' => 'Saved',
            'message' => 'Data saved successfully',
            'data' => [
                'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
            ],
        ]);
    }

    public function update()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['edit']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        if ($id <= 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Invalid Record ID',
                'data' => [],
            ]);
        }

        $b = $this->db->table($this->table);
        $b->select('status');
        $b->where($this->pfield, $id);
        $rec = $b->get()->getRow();

        if (!$rec || (int) $rec->status !== 1) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Only pending nominations can be updated',
                'data' => [],
            ]);
        }

        [$valid, $message, $payload] = $this->validate_nomination_payload($id);
        if (!$valid) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => $message,
                'data' => [],
            ]);
        }

        $header = [
            'actID' => $payload['actID'],
        ];

        $this->db->transStart();

        $wasChange = $logModel->field_logs($this->sub, $this->table, $this->pfield, $id, 'Update', $header);

        $b = $this->db->table($this->table);
        $b->set($header);
        $b->where($this->pfield, $id);
        if (!$b->update()) {
            $this->db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'title' => 'Error',
                'message' => $this->db->error()['message'],
                'data' => [],
            ]);
        }

        if ($wasChange) {
            $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Update', 'Record - ' . $this->sub . ' Update');
        }

        $this->db->table(PREFIX . 'nomination_details')->where('id', $id)->delete();

        $detail_table = $this->db->table(PREFIX . 'nomination_details');
        foreach ($payload['empIDs'] as $emp_id) {
            if (!$detail_table->insert([
                'id' => $id,
                'empID' => $emp_id,
                'type' => $payload['nominationType'],
                'status' => 1,
            ])) {
                $this->db->transRollback();

                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 500,
                    'title' => 'Error',
                    'message' => $this->db->error()['message'],
                    'data' => [],
                ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'title' => 'Error',
                'message' => $this->db->error()['message'],
                'data' => [],
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'title' => 'Saved',
            'message' => 'Data updated successfully',
            'data' => [
                'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
            ],
        ]);
    }

    private function get_session_filters($controller)
    {
        $filters = [];
        foreach ($this->get_conditions() as $key) {
            $filters[$key['variable']] = (string) $this->session->get($controller . '_' . $key['variable']);
        }

        return $filters;
    }

    private function get_list_records_for_export()
    {
        $controller = strtolower(str_replace(' ', '_', $this->module)) . '_' . strtolower(str_replace(' ', '_', $this->sub));
        $filters = $this->get_session_filters($controller);
        $sortby = (string) $this->session->get($controller . '_sortby');
        $sortorder = (string) $this->session->get($controller . '_sortorder');
        $limit = (int) $this->session->get($controller . '_limit');
        $page = (int) $this->session->get($controller . '_page');
        $sorting_fields = [
            'dateCreated' => 'desc',
            'id' => 'desc',
        ];

        $b = $this->base_list_builder();
        $this->apply_list_filters($b, $filters);

        if ($sortby && $sortorder) {
            $b->orderBy($sortby, $sortorder);
            foreach ($sorting_fields as $field => $default_order) {
                if ($field !== $sortby) {
                    $b->orderBy($field, $default_order);
                }
            }
        } else {
            foreach ($sorting_fields as $field => $default_order) {
                $b->orderBy($field, $default_order);
            }
        }

        $offset = 0;
        if ($limit > 0) {
            $offset = max($page - 1, 0) * $limit;
            $b->limit($limit, $offset);
        }

        return [
            'records' => $b->get()->getResult(),
            'filters' => $filters,
            'sortby' => $sortby,
            'sortorder' => $sortorder,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    public function printlist()
    {
        $this->submenu();
        $data = $this->data;

        if (!$this->roles['export']) {
            $data['page_title'] = 'Unauthorized';
            $data['page_desc'] = 'You dont have access to this page';
            $data['url'] = $this->controller_page;
            echo view('unauthorize', $data);
            return;
        }

        $list_data = $this->get_list_records_for_export();
        $data['sortby'] = $list_data['sortby'];
        $data['sortorder'] = $list_data['sortorder'];
        $data['limit'] = $list_data['limit'];
        $data['offset'] = $list_data['offset'];
        $data['headers'] = generate_headers($this->get_conditions());
        $data['records'] = $this->build_list_data($list_data['records']);
        $data['title'] = strtoupper($this->sub . ' List');

        $html = '';
        $html .= view('printlist_header', $data);
        $html .= view($this->module_path . '/printlist');
        $html .= view('printlist_footer');

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        unset($fontDirs, $fontData);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 35,
            'margin_bottom' => 30,
            'pdf_version' => 1.7,
            'default_font_size' => 12,
            'orientation' => 'P',
            'default_font' => 'arial',
            'curlAllowUnsafeSslRequests' => true,
        ]);

        $mpdf->showImageErrors = true;
        $mpdf->debug = true;

        $fileUrl = base_url('assets/images/print/print_header_landscape.png');
        $mpdf->SetHTMLHeader('<div style="text-align:center;"><img style="width:700px;" src="' . $fileUrl . '?" alt=""></div>');
        $mpdf->SetHTMLFooter('<div style="border-top:1px solid #000; font-size:10px; padding-top:4px;"><table width="100%" style="border:none; border-collapse:collapse;"><tr><td align="left" style="border:none;">Date Printed: ' . date('M d, Y h:i A') . '</td><td align="right" style="border:none;">Page {PAGENO} of {nb}</td></tr></table></div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        die;
    }

    public function exportlist()
    {
        $this->submenu();
        $data = $this->data;

        if (!$this->roles['export']) {
            $data['page_title'] = 'Unauthorized';
            $data['page_desc'] = 'You dont have access to this page';
            $data['url'] = $this->controller_page;
            echo view('unauthorize', $data);
            return;
        }

        $list_data = $this->get_list_records_for_export();
        $data['sortby'] = $list_data['sortby'];
        $data['sortorder'] = $list_data['sortorder'];
        $data['limit'] = $list_data['limit'];
        $data['offset'] = $list_data['offset'];
        $data['headers'] = $headers = generate_headers($this->get_conditions());
        $build_list = $this->build_list_data($list_data['records']);
        $data['title'] = strtoupper($this->sub . ' List');

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        for ($i = 1; $i <= 7; $i++) {
            $sheet->mergeCells('A' . $i . ':' . chr(64 + count($headers)) . $i);
        }

        $sheet->setCellValue('A2', $data['company_name'] ?? 'Company Name');
        $sheet->getStyle('A2')->getFont()->setSize(15)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A3', $data['company_address'] ?? 'Company Address');
        $sheet->getStyle('A3')->getFont()->setSize(12);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A4', $data['contact_number'] ?? 'Tel No. 000-0000');
        $sheet->getStyle('A4')->getFont()->setSize(12);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A6', $data['title']);
        $sheet->getStyle('A6')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');

        $row = 8;
        $colIndex = 0;
        foreach ($headers as $header) {
            $col = chr(ord('A') + $colIndex);
            $sheet->setCellValue($col . $row, $header['column_header']);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal($header['align'] ?? 'left');
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);

            if (!empty($header['excel_width']) && is_numeric($header['excel_width'])) {
                $sheet->getColumnDimension($col)->setWidth($header['excel_width']);
            }

            $colIndex++;
        }

        $row++;
        foreach ($build_list as $rec) {
            $colIndex = 0;
            foreach ($rec as $field) {
                $col = chr(ord('A') + $colIndex);
                $sheet->setCellValue($col . $row, strip_tags($field['text']));
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal($field['align'] ?? 'left');
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $colIndex++;
            }
            $row++;
        }

        $sheet->setTitle($data['title']);

        $writer = new Xlsx($spreadsheet);
        $filename = strtolower(str_replace(' ', '_', $this->sub . '_list_' . date('His')));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $writer->save('php://output');
        exit();
    }

    public function replace_attachments($id)
    {
        $this->submenu();
        $data = $this->data;
        $id = (int) $this->encrypter->decode($id);

        if (!$this->roles['edit']) {
            $data['page_title'] = 'Unauthorized';
            $data['page_desc'] = 'You dont have access to this page';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $rec = $this->get_nomination_record($id);
        if (!$rec) {
            $data['page_title'] = 'Not Found';
            $data['page_desc'] = 'Record was not found';
            $data['url'] = $this->controller_page;

            echo view('header', $data);
            echo view('default');
            echo view('footer');
            return;
        }

        $data['rec'] = $rec;

        echo view('header', $data);
        echo view($this->module_path . '/replace_files');
        echo view('footer');
    }

    public function replace_attachments_save()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['edit']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Replace Attachments',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        $rec = $this->get_nomination_record($id);

        if (!$rec || (int) $rec->actID <= 0) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 404,
                'title' => 'Replace Attachments',
                'message' => 'Record not found',
                'data' => [],
            ]);
        }

        $files = $this->request->getFiles();
        if (!isset($files['files']) || count($files['files']) === 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Replace Attachments',
                'message' => 'Please upload at least one file',
                'data' => [],
            ]);
        }

        if (count($files['files']) > 10) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Replace Attachments',
                'message' => 'Please upload at most 10 files',
                'data' => [],
            ]);
        }

        foreach ($files['files'] as $file) {
            if (!$file->isValid()) {
                $message = $file->getErrorString() . ' (' . $file->getError() . ')';
                if ($file->getError() === UPLOAD_ERR_INI_SIZE || $file->getError() === UPLOAD_ERR_FORM_SIZE) {
                    $message = 'File size exceeds server limit.';
                }

                return $this->response->setStatusCode(409)->setJSON([
                    'status' => 409,
                    'title' => 'Upload Error',
                    'message' => $message,
                    'data' => [],
                ]);
            }

            if ($file->getSize() >= 52428800) {
                return $this->response->setStatusCode(409)->setJSON([
                    'status' => 409,
                    'title' => 'Replace Attachments',
                    'message' => 'File size must not be greater than 50 mb',
                    'data' => [],
                ]);
            }
        }

        $upload_dir = FCPATH . $this->dir . md5($rec->actID) . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $old_files = array_values(array_filter(array_map('trim', explode(',', (string) ($rec->files ?? '')))));
        foreach ($old_files as $old) {
            $old_path = $upload_dir . $old;
            if (is_file($old_path)) {
                @unlink($old_path);
            }
        }

        $updated_files = [];
        $ctr = 1;
        foreach ($files['files'] as $file) {
            $new_name = 'file_' . $ctr . '.' . $file->getClientExtension();
            if (!$file->hasMoved()) {
                $file->move($upload_dir, $new_name, true);
                $updated_files[] = $new_name;
                $ctr++;
            }
        }

        $implodeFiles = implode(',', $updated_files);
        $activity_table = PREFIX . 'activities';
        $wasChange = $logModel->field_logs($this->sub, $activity_table, 'actID', $rec->actID, 'Update', ['files' => $implodeFiles]);

        $b = $this->db->table($activity_table);
        $b->set('files', $implodeFiles);
        $b->where('actID', $rec->actID);

        if (!$b->update()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'title' => 'Replace Attachments',
                'message' => 'Unable to update record',
                'data' => [],
            ]);
        }

        if ($wasChange) {
            $logModel->table_logs($this->sub, $activity_table, 'actID', $rec->actID, 'Update', 'Record - Activity Attachments Update');
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'title' => 'Saved',
            'message' => 'Attachments replaced successfully',
            'data' => [
                'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
            ],
        ]);
    }

    private function update_details_status($id, $status)
    {
        $b = $this->db->table(PREFIX . 'nomination_details');
        $b->set('status', $status);
        $b->where('id', $id);
        $b->update();
    }

    public function cancel()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['confirm']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        $b = $this->db->table($this->table);
        $b->select('status');
        $b->where($this->pfield, $id);
        $b->whereIn('status', [1, 2, 3]);
        $rec = $b->get()->getRow();

        if (!$rec) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Data cannot be cancelled',
                'data' => [],
            ]);
        }

        $fields = [
            'status' => 0,
            'cancelledBy' => $this->session->current_user->userID,
            'dateCancelled' => date('Y-m-d H:i:s'),
        ];

        $wasChange = $logModel->field_logs($this->sub, $this->table, $this->pfield, $id, 'Update', $fields);
        $b = $this->db->table($this->table);
        $b->set($fields);
        $b->where($this->pfield, $id);

        if ($b->update()) {
            $this->update_details_status($id, 0);

            if ($wasChange) {
                $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Update', 'Record - ' . $this->sub);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'title' => 'Saved',
                'message' => 'Data cancelled successfully',
                'data' => [
                    'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
                ],
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'status' => 500,
            'title' => 'Saving data',
            'message' => 'Unable to cancel record',
            'data' => [],
        ]);
    }

    public function review()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['review']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        $b = $this->db->table($this->table);
        $b->select('status');
        $b->where($this->pfield, $id);
        $b->where('status', 1);
        $rec = $b->get()->getRow();

        if (!$rec) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Data cannot be Reviewed',
                'data' => [],
            ]);
        }

        $fields = [
            'reviewedBy' => $this->session->current_user->userID,
            'dateReviewed' => date('Y-m-d H:i:s'),
            'status' => 2,
        ];

        $wasChange = $logModel->field_logs($this->sub, $this->table, $this->pfield, $id, 'Update', $fields);
        $b = $this->db->table($this->table);
        $b->set($fields);
        $b->where($this->pfield, $id);

        if ($b->update()) {
            $this->update_details_status($id, 2);

            if ($wasChange) {
                $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Update', 'Record - ' . $this->sub . ' Reviewed');
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'title' => 'Saved',
                'message' => 'Data Reviewed successfully',
                'data' => [
                    'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
                ],
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'status' => 500,
            'title' => 'Saving data',
            'message' => 'Unable to review record',
            'data' => [],
        ]);
    }

    public function approve()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['approve']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        $b = $this->db->table($this->table);
        $b->select('status');
        $b->where($this->pfield, $id);
        $b->where('status', 2);
        $rec = $b->get()->getRow();

        if (!$rec) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Data cannot be Approved',
                'data' => [],
            ]);
        }

        $fields = [
            'approvedBy' => $this->session->current_user->userID,
            'dateApproved' => date('Y-m-d H:i:s'),
            'status' => 3,
        ];

        $wasChange = $logModel->field_logs($this->sub, $this->table, $this->pfield, $id, 'Update', $fields);
        $b = $this->db->table($this->table);
        $b->set($fields);
        $b->where($this->pfield, $id);

        if ($b->update()) {
            $this->update_details_status($id, 3);

            if ($wasChange) {
                $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Update', 'Record - ' . $this->sub . ' Approved');
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'title' => 'Saved',
                'message' => 'Data Approved successfully',
                'data' => [
                    'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
                ],
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'status' => 500,
            'title' => 'Saving data',
            'message' => 'Unable to approve record',
            'data' => [],
        ]);
    }

    public function decline()
    {
        $this->submenu();
        $logModel = new LogModel();

        if (!$this->roles['decline']) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'You dont have access to this action',
                'data' => [],
            ]);
        }

        $id = (int) $this->encrypter->decode((string) $this->request->getPost($this->pfield));
        $b = $this->db->table($this->table);
        $b->select('status');
        $b->where($this->pfield, $id);
        $b->whereNotIn('status', [0, -1]);
        $rec = $b->get()->getRow();

        if (!$rec) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 409,
                'title' => 'Saving data',
                'message' => 'Data cannot be Declined',
                'data' => [],
            ]);
        }

        $fields = [
            'declinedBy' => $this->session->current_user->userID,
            'dateDeclined' => date('Y-m-d H:i:s'),
            'status' => -1,
        ];

        $wasChange = $logModel->field_logs($this->sub, $this->table, $this->pfield, $id, 'Update', $fields);
        $b = $this->db->table($this->table);
        $b->set($fields);
        $b->where($this->pfield, $id);

        if ($b->update()) {
            $this->update_details_status($id, -1);

            if ($wasChange) {
                $logModel->table_logs($this->sub, $this->table, $this->pfield, $id, 'Update', 'Record - ' . $this->sub . ' Declined');
            }

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 200,
                'title' => 'Saved',
                'message' => 'Data Declined successfully',
                'data' => [
                    'url' => $this->controller_page . '/view/' . $this->encrypter->encode($id),
                ],
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'status' => 500,
            'title' => 'Saving data',
            'message' => 'Unable to decline record',
            'data' => [],
        ]);
    }

    public function search_employees()
    {
        $value = trim((string) $this->request->getGet('value'));
        $employee_types = $this->sanitize_employee_type_ids((array) $this->request->getGet('employeeTypes'));

        $b = $this->db->table('hris_employees');
        $b->select('hris_employees.empID');
        $b->select('hris_employees.fname');
        $b->select('hris_employees.mname');
        $b->select('hris_employees.lname');
        $b->select('hris_employees.empNo');
        $b->join('hris_employments', 'hris_employments.empID = hris_employees.empID', 'left');
        $b->groupStart();
        $b->like('hris_employees.lname', $value, 'both');
        $b->orLike('hris_employees.fname', $value, 'both');
        $b->orLike('hris_employees.empNo', $value, 'both');
        $b->groupEnd();
        $b->where('hris_employees.status', 1);
        $b->where('hris_employments.status', 1);

        if (!empty($employee_types)) {
            $b->whereIn('hris_employments.employeeTypeID', $employee_types);
        }

        $b->groupBy([
            'hris_employees.empID',
            'hris_employees.fname',
            'hris_employees.mname',
            'hris_employees.lname',
            'hris_employees.empNo',
        ]);
        $b->orderBy('hris_employees.lname', 'asc');
        $b->orderBy('hris_employees.fname', 'asc');

        $final_records = [];
        foreach ($b->get()->getResult() as $rec) {
            $final_records[] = [
                'id' => $this->encrypter->encode($rec->empID),
                'text' => $rec->empNo . ' - ' . $rec->lname . ', ' . $rec->fname . ' ' . $rec->mname,
            ];
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'title' => 'Success',
            'message' => empty($final_records) ? 'No records found' : 'Found records',
            'data' => [
                'records' => $final_records,
            ],
        ]);
    }
}
