<?php
// Template for splitting a ticket into a child ticket (Freshdesk-style
// parent-child ticketing).  Displayed in a modal dialog when an agent
// chooses to create a child ticket from the current ticket.

$namespace = sprintf('ticket.%d.split', $ticket->getId());
$info = $info ?: array();
$errors = $info['errors'] ?: array();
?>
<div id="split-ticket-form">
<h3 class="drag-handle"><?php echo Format::htmlchars($info['title'] ?: __('Create Child Ticket')); ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<hr/>
<?php
if ($info['error']) {
    echo sprintf('<p id="msg_error">%s</p>', Format::htmlchars($info['error']));
} elseif ($info['warning']) {
    echo sprintf('<p id="msg_warning">%s</p>', Format::htmlchars($info['warning']));
} elseif ($info['msg']) {
    echo sprintf('<p id="msg_notice">%s</p>', Format::htmlchars($info['msg']));
}
?>
<div style="display:block; margin:5px;">
<form method="post" name="split-ticket" id="split-ticket"
    action="<?php echo $info['action'] ?: '#'; ?>">
    <?php csrf_token(); ?>
    <table width="100%">
        <tbody>
            <tr>
                <td><strong><?php echo __('Subject'); ?>:</strong>
                    <font class="error"><b>*</b></font>
                </td>
                <td>
                    <input type="text" name="subject" size="40"
                        value="<?php echo Format::htmlchars($_POST['subject'] ?? ''); ?>"
                        autofocus />
                    &nbsp;<font class="error"><?php echo $errors['subject'] ?? ''; ?></font>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo __('Department'); ?>:</strong></td>
                <td>
                    <select name="deptId">
                        <option value="">&mdash; <?php echo __('Select Department'); ?> &mdash;</option>
                        <?php
                        $selectedDept = intval($_POST['deptId'] ?? $ticket->getDeptId());
                        if ($depts = $thisstaff->getDepartmentNames(true)) {
                            foreach ($depts as $id => $name) {
                                if (!($role = $thisstaff->getRole($id))
                                    || !$role->hasPerm(Ticket::PERM_CREATE)
                                ) continue;
                                printf('<option value="%d" %s>%s</option>',
                                    $id,
                                    ($selectedDept == $id) ? 'selected="selected"' : '',
                                    Format::htmlchars($name));
                            }
                        }
                        ?>
                    </select>
                    &nbsp;<font class="error"><?php echo $errors['deptId'] ?? ''; ?></font>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo __('Help Topic'); ?>:</strong>
                    <font class="error"><b>*</b></font>
                </td>
                <td>
                    <select name="topicId">
                        <option value="">&mdash; <?php echo __('Select Help Topic'); ?> &mdash;</option>
                        <?php
                        $selectedTopic = intval($_POST['topicId'] ?? $ticket->getTopicId());
                        foreach (Topic::getHelpTopics(false, Topic::DISPLAY_DISABLED) as $id => $name) {
                            printf('<option value="%d" %s>%s</option>',
                                $id,
                                ($selectedTopic == $id) ? 'selected="selected"' : '',
                                Format::htmlchars($name));
                        }
                        ?>
                    </select>
                    &nbsp;<font class="error"><?php echo $errors['topicId'] ?? ''; ?></font>
                </td>
            </tr>
            <tr>
                <td><strong><?php echo __('Description'); ?>:</strong></td>
                <td>
                    <textarea name="message" rows="5" cols="40"
                        placeholder="<?php echo __('Describe the sub-issue to be worked on'); ?>"
                        ><?php echo Format::htmlchars($_POST['message'] ?? ''); ?></textarea>
                    &nbsp;<font class="error"><?php echo $errors['message'] ?? ''; ?></font>
                </td>
            </tr>
        </tbody>
    </table>
    <hr>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="reset" value="<?php echo __('Reset'); ?>">
            <input type="button" name="cancel" class="close"
                value="<?php echo __('Cancel'); ?>">
        </span>
        <span class="buttons pull-right">
            <input type="submit" value="<?php echo __('Create Child Ticket'); ?>">
        </span>
    </p>
</form>
</div>
<div class="clear"></div>
</div>
