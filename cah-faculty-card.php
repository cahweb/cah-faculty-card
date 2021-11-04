<?php
/**
 * Plugin Name: CAH Faculty Information Card (Single)
 * Description: Provides a shortcode that will display a single faculty member's photo and information on a page.
 * Author: Mike W. Leavitt
 * Version: 0.1.0
 */
defined('ABSPATH') or die('No direct access plzthx');

// set constants
define('CAH_FACULTY_CARD__VERSION', '0.1.0');
define('CAH_FACULTY_CARD__PLUGIN_FILE', __FILE__);
define('CAH_FACULTY_CARD__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CAH_FACULTY_CARD__PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

require_once 'dbconfig.php';

if (!class_exists('CAH_FacultyCard')) {
    class CAH_FacultyCard
    {

        private $db_helper;
        private $version;

        public function __construct()
        {
            $this->db_helper = new CAH_DBHelper();
            $this->version = CAH_FACULTY_CARD__VERSION;
        }

        public function setup()
        {
            add_shortcode('cah-faculty-card', [ $this, 'shortcode' ]);
            add_action('wp_enqueue_scripts', [ $this, 'registerStyle' ], 5, 0);
            add_action('wp_enqueue_scripts', [ $this, 'loadStyle' ], 10, 0);
        }

        public function shortcode($atts = array()) : string
        {
            extract(
                shortcode_atts(
                    array(
                        'id' => 0,
                        'class' => '',
                        'img_shape' => 'rounded',
                        'interests' => 'false',
                    ),
                    $atts
                )
            );

            $interests = 'true' == $interests ? true : false;
            $id = intval($id);

            if ($id == 0) {
                return '';
            }

            $info = $this->getInfo($id);

            if (is_null($info)) {
                $text = '[Problem finding faculty information.]';

                // For Debug
                /*
                if ($this->db_helper->errorCode) {
                    $text = "Error " . $this->db_helper->errorCode . ": " . $this->db_helper->errorMsg;
                }
                */

                return "<p>$text</p>";
            }

            $title = '';
            if (preg_match("/^\s*of\s/", $info['titleDept'])) {
                $title = "{$info['title']} {$info['titleDept']}";
            } elseif (preg_match("/^\s*of\s/", $info['titleDeptShort'])) {
                $title = "{$info['title']} {$info['titleDeptShort']}";
            } else {
                if (isset($info['titleDeptShort']) && !empty($info['titleDeptShort'])) {
                    $title = $info['titleDeptShort'];
                } elseif (!isset($info['titleDeptShort']) || empty($info['titleDeptShort'])) {
                    $title = $info['title'];
                } elseif (isset($info['titleDept']) && !empty($info['titleDept'])) {
                    $title = $info['titleDept'];
                }
            }

            $title = preg_replace("/<br\s?\/?>/", ', ', $title);

            ob_start();
            ?>

            <div class="cah-staff-list mx-0 <?= !empty($class) ? $class : '' ?>">
                <a href="<?= home_url("faculty-staff?id=$id") ?>" class="d-flex flex-row mx-0">
                    <div class="staff-list d-flex align-items-center">
                        <div class="faculty-img">
                            <img src="https://cah.ucf.edu/common/resize.php?filename=<?= $info['photoUrl'] ?>&sz=2" alt="<?= $info['fullName'] ?>" class="mr-2 img-circle <?= 'rounded' == $img_shape ? $img_shape :'' ?>">
                        </div>
                        <div class="d-flex flex-column">
                            <p class="staff-name">
                                <strong><?= $info['fullName'] ?></strong>
                            </p>
                            <div class="fs-list">
                                <small>
                                    <p class="staff-title"><em><?= shorten_title($title) ?></em></p>
                                    <p class="staff-email">
                                        <a href="mailto:<?= $info['email'] ?>"><?= $info['email'] ?></a>
                                    </p>
                                </small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <?php
            return ob_get_clean();
        }

        public function registerStyle()
        {
            wp_register_style(
                'cah-faculty-card-style',
                CAH_FACULTY_CARD__PLUGIN_DIR_URL . 'css/style.css',
                [],
                $this->version,
                'all'
            );
        }


        public function loadStyle()
        {
            global $post;
            if (!isset($post) || !is_object($post)) {
                return;
            }

            if (stripos($post->post_content, '[cah-faculty-card') !== false) {
                wp_enqueue_style('cah-faculty-card-style');
            }
        }


        private function getInfo(int $id) : ?array
        {
            $result = $this->db_helper->query($this->sql($id));

            if ($result instanceof mysqli_result && $result->num_rows > 0) {
                return mysqli_fetch_assoc($result);
            }

            return null;
        }

        private function sql(int $id) : string
        {
            return "SELECT DISTINCT u.id, u.lname, u.fname, CONCAT_WS(' ', u.fname, u.mname, u.lname) AS fullName, u.email, u.phone, t.description AS title, ud.prog_title_dept AS titleDept, ud.prog_title_dept_short AS titleDeptShort, t.title_group AS titleGroup, IF(u.photo_extra IS NOT NULL, CONCAT(u.photo_path, u.photo_extra), u.photo_path) AS photoUrl, u.interests, a.academic_id AS subDept, ac.description AS subDeptName, u.activities, u.awards, u.research, u.has_cv, u.homepage, u.biography AS bio, u.office, r.room, r.`desc`, r.building FROM cah.users AS u LEFT JOIN cah.users_departments AS ud ON u.id = ud.user_id LEFT JOIN cah.titles AS t ON t.id = ud.title_id LEFT JOIN cah.academics AS a ON a.user_id = u.id LEFT JOIN cah.academic_categories AS ac ON ac.id = a.academic_id LEFT JOIN ( SELECT rooms.id, rooms.room_number AS room, buildings.short_description AS `desc`, buildings.building_number AS building FROM cah.rooms LEFT JOIN buildings ON rooms.building_id = buildings.id) AS r ON r.id = u.room_id WHERE u.id = $id AND u.active = 1 AND u.show_web = 1 AND ud.affiliation = 'active' LIMIT 1";
        }
    }
}

$fcard = new CAH_FacultyCard();

function shorten_title($title)
{
    if (strlen($title) > 75) {
        $cut_title = rtrim(substr($title, 0, 75));
        $cut_title .= "&nbsp;.&nbsp;.&nbsp;.";
        return $cut_title;
    } else {
        return $title;
    }
}

add_action('init', [$fcard, 'setup'], 10, 0);
