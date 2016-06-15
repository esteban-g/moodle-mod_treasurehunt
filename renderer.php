<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/treasurehunt/locallib.php');

class mod_treasurehunt_renderer extends plugin_renderer_base {

    /**
     * Utility function to add a row of data to a table with 2 columns. Modified
     * the table param and does not return a value
     *
     * @param html_table $table The table to append the row of data to
     * @param array $text Array with the text of each cell
     * @param bool $header If cells are header or not
     * @param array $class Array with the class of each cell
     * @param array $colspan Array with the class of each cell
     * @return void
     */
    private function add_table_row(html_table $table, array $text, $header, array $class = null, array $colspan = null) {
        $row = new html_table_row();
        $cells = array();
        for ($i = 0, $f = count($text); $i < $f; $i++) {
            $cell = new html_table_cell($text[$i]);
            if ($header) {
                $cell->header = true;
            }
            if (isset($class)) {
                $cell->attributes['class'] = $class[$i];
            }
            if (isset($colspan)) {
                $cell->colspan = $colspan[$i];
            }
            array_push($cells, $cell);
        }
        $row->cells = $cells;
        $table->data[] = $row;
    }

    /**
     * Defer to template.                                                                                                           
     *                                                                                                                              
     * @param index_page $page                                                                                                      
     *                                                                                                                              
     * @return string html for the page                                                                                             
     */
    public function render_play_page(\mod_treasurehunt\output\play_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_treasurehunt/play', $data);
    }

    /**
     * Render a table containing the current status of the grading process.
     *
     * @param treasurehunt_user_historical_riddles  $historical
     * @return string
     */
    public function render_treasurehunt_user_historical_attempts(treasurehunt_user_historical_attempts $historical) {
        // Create a table for the data.
        $o = '';
        $o .= $this->output->container_start('historicalattempts');
        $o .= $this->output->heading(get_string('historicalattempts', 'treasurehunt', $historical->username), 3);
        $o .= $this->output->box_start('boxaligncenter gradingsummarytable');
        // Status.
        if (count($historical->attempts)) {
            $numattempt = 1;
            $t = new html_table();
            $this->add_table_row($t, array(get_string('attempt', 'treasurehunt'), get_string('state', 'treasurehunt')), true);
            foreach ($historical->attempts as $attempt) {
                if (!$attempt->penalty) {
                    $class = 'successfulattempt';
                } else {
                    $class = 'failedattempt';
                }
                $this->add_table_row($t, array($numattempt++, $attempt->string), false, array($class, ''));
            }
            // All done - write the table.
            $o .= html_writer::table($t);
        } else {
            if ($historical->teacherreview) {
                $o .= $this->output->notification(get_string('nouserattempts', 'treasurehunt', $historical->username));
            } else {
                $o .= $this->output->notification(get_string('noattempts', 'treasurehunt'));
            }
        }
        // Si no ha finalizado pongo el botón de jugar
        $urlparams = array('id' => $historical->coursemoduleid);
        if ($historical->outoftime || $historical->roadfinished) {
            $string = get_string('reviewofplay', 'treasurehunt');
        } else {
            $string = get_string('play', 'treasurehunt');
        }
        if ((count($historical->attempts) || !$historical->outoftime) && !$historical->teacherreview) {
            $o .= $this->output->single_button(new moodle_url('/mod/treasurehunt/play.php', $urlparams), $string, 'get');
        }
        $o .= $this->output->box_end();

        // Close the container and insert a spacer.
        $o .= $this->output->container_end();

        return $o;
    }

    /**
     * Render a table containing the current status of the grading process.
     *
     * @param treasurehunt_user_progress $progress
     * @return string
     */
    public function render_treasurehunt_users_progress(treasurehunt_users_progress $progress) {
        // Create a table for the data.
        $o = '';
        $s = '';
        if (!count($progress->roadsusersprogress) && $progress->managepermission) {
            $s .= $this->output->notification(get_string('noroads', 'treasurehunt'));
        } else {
            if (count($progress->duplicategroupsingroupings) && $progress->managepermission) {
                $s .= $this->output->notification(get_string('warnusersgrouping', 'treasurehunt', implode(",", $progress->duplicategroupsingroupings)));
            }
            if (count($progress->duplicateusersingroups) && $progress->managepermission) {
                $s .= $this->output->notification(get_string('warnusersgroup', 'treasurehunt', implode(",", $progress->duplicateusersingroups)));
            }
            if (count($progress->noassignedusers) && $progress->managepermission) {
                $s .= $this->output->notification(get_string('warnusersoutside', 'treasurehunt', implode(",", $progress->noassignedusers)));
            }
            foreach ($progress->roadsusersprogress as $roadusersprogress) {
                if ($roadusersprogress->validated) {
                    if (count($roadusersprogress->userlist)) {
                        $s .= $this->output->heading($roadusersprogress->name, 4);
                        $s .= $this->output->box_start('boxaligncenter usersprogresstable');
                        $t = new html_table();
                        if ($progress->groupmode) {
                            $title = get_string('group', 'treasurehunt');
                        } else {
                            $title = get_string('user', 'treasurehunt');
                        }
                        $this->add_table_row($t, array($title, get_string('riddles', 'treasurehunt')), true, null, array(null, $roadusersprogress->totalriddles));
                        foreach ($roadusersprogress->userlist as $user) {
                            $row = new html_table_row();
                            if ($progress->groupmode) {
                                $name = $user->name;
                                if ($progress->viewpermission) {
                                    $params = array('id' => $progress->coursemoduleid, 'groupid' => $user->id);
                                    $url = new moodle_url('/mod/treasurehunt/view.php', $params);
                                    $name = html_writer::link($url, $name);
                                }
                            } else {
                                $name = fullname($user);
                                if ($progress->viewpermission) {
                                    $params = array('id' => $progress->coursemoduleid, 'userid' => $user->id);
                                    $url = new moodle_url('/mod/treasurehunt/view.php', $params);
                                    $name = html_writer::link($url, $name);
                                }
                            }
                            $cells = array($name);
                            for ($i = 1; $i <= $roadusersprogress->totalriddles; $i++) {
                                $cell = new html_table_cell($i);
                                if (isset($user->ratings[$i])) {
                                    $cell->attributes['class'] = $user->ratings[$i]->class;
                                } else {
                                    $cell->attributes['class'] = 'noattempt';
                                }
                                array_push($cells, $cell);
                            }
                            $row->cells = $cells;
                            $t->data[] = $row;
                        }
                        // All done - write the table.
                        $s .= html_writer::table($t);
                        $s .= $this->output->box_end();
                    } else {
                        if ($progress->managepermission) {
                            $s .= $this->output->heading($roadusersprogress->name, 4);
                            if ($progress->groupmode) {
                                $notification = get_string('nogroupassigned', 'treasurehunt');
                            } else {
                                $notification = get_string('nouserassigned', 'treasurehunt');
                            }
                            $s .= $this->output->notification($notification);
                        }
                    }
                } else {
                    if ($progress->managepermission) {
                        $s .= $this->output->heading($roadusersprogress->name, 4);
                        $s .= $this->output->notification(get_string('invalroadid', 'treasurehunt'));
                    }
                }
            }
        }
        if ($progress->managepermission) {
            $urlparams = array('id' => $progress->coursemoduleid);
            $s .= $this->output->single_button(new moodle_url('/mod/treasurehunt/edit.php', $urlparams), get_string('edittreasurehunt', 'treasurehunt'), 'get');
        }
        if ($s !== '') {
            $o .= $this->output->container_start('usersprogress');
            $o .= $this->output->heading_with_help(get_string('usersprogress', 'treasurehunt'), 'usersprogress', 'treasurehunt', null, null, 3);
            $o .= $s;
            // Close the container and insert a spacer.
            $o .= $this->output->container_end();
        }


        return $o;
    }

    /**
     * Render a table containing the current status of the grading process.
     *
     * @param treasurehunt_user_progress $progress
     * @return string
     */
    public function render_treasurehunt_info(treasurehunt_info $info) {
        // Create a table for the data.
        $o = '';
        $notavailable = false;
        $o .= $this->output->container_start('treasurehuntinfo');
        if ($info->timenow < $info->treasurehunt->allowattemptsfromdate) {
            $notavailable = true;
            $message = get_string('treasurehuntnotavailable', 'treasurehunt', userdate($info->treasurehunt->allowattemptsfromdate));
            $o .= html_writer::tag('p', $message) . "\n";
            if ($info->treasurehunt->cutoffdate) {
                $message = get_string('treasurehuntcloseson', 'treasurehunt', userdate($info->treasurehunt->cutoffdate));
                $o .= html_writer::tag('p', $message) . "\n";
            }
        } else if ($info->treasurehunt->cutoffdate && $info->timenow > $info->treasurehunt->cutoffdate) {
            $message = get_string('treasurehuntclosed', 'treasurehunt', userdate($info->treasurehunt->cutoffdate));
            $o .= html_writer::tag('p', $message) . "\n";
        } else {
            if ($info->treasurehunt->allowattemptsfromdate) {
                $message = get_string('treasurehuntopenedon', 'treasurehunt', userdate($info->treasurehunt->allowattemptsfromdate));
                $o .= html_writer::tag('p', $message) . "\n";
            }
            if ($info->treasurehunt->cutoffdate) {
                $message = get_string('treasurehuntcloseson', 'treasurehunt', userdate($info->treasurehunt->cutoffdate));
                $o .= html_writer::tag('p', $message) . "\n";
            }
        }
        if ($info->treasurehunt->grade > 0) {
            $options = treasurehunt_get_grading_options();
            $message = get_string('grademethodinfo', 'treasurehunt', $options[$info->treasurehunt->grademethod]);
            $o .= html_writer::tag('p', $message . $this->help_icon('grademethod', 'treasurehunt')) . "\n";
        }
        if ($notavailable) {
            $urlparams = array('id' => $info->courseid);
            $o .= $this->output->single_button(new moodle_url('/course/view.php', $urlparams), get_string('backtocourse', 'treasurehunt'), 'get', array('class' => 'continuebutton'));
        }
        // Close the container and insert a spacer.
        $o .= $this->output->container_end();

        return $o;
    }

}
