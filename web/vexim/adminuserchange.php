<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  $query = "SELECT * FROM users WHERE user_id={$_GET['user_id']}";
  $result = $db->query($query);
  if ($result->numRows()) { $row = $result->fetchRow(); }
  $username = $row[username];
  $domquery = "SELECT avscan,spamassassin,quotas,pipe FROM domains
    WHERE domain_id={$_SESSION['domain_id']}";
  $domresult = $db->query($domquery);
  if ($domresult->numRows()) {
    $domrow = $domresult->fetchRow();
  }
  $blockquery = "SELECT blockhdr,blockval,block_id FROM blocklists,users
    WHERE blocklists.user_id='{$_GET['user_id']}'
    AND users.user_id=blocklists.user_id";
  $blockresult = $db->query($blockquery);
?>
<html>
  <head>
    <title><?php echo _('Virtual Exim') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.userchange.realname.focus()">
  <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="menu">
      <a href="adminuser.php"><?php echo _('Manage Accounts'); ?></a><br>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="forms">
    <table align="center">
      <form name="userchange" method="post" action="adminuserchangesubmit.php">
        <tr>
          <td><?php echo _('Name'); ?>:</td>
          <td>
            <input type="text" size="25" name="realname"
              value="<?php print $row['realname']; ?>" class="textfield">
          </td>
        </tr>
        <tr>
          <td><?php echo _('Email Address'); ?>:</td>
          <td><?php print $row['username']; ?></td>
        </tr>
        <input name="user_id" type="hidden"
          value="<?php print $_GET['user_id']; ?>" class="textfield">
        <tr>
          <td><?php echo 'Password'; ?>:</td>
          <td>
            <input type="password" size="25" name="clear" class="textfield">
          </td>
        </tr>
        <tr>
          <td><?php echo _('Verify Password'); ?>:</td>
          <td>
            <input type="password" size="25" name="vclear" class="textfield">
          </td>
        </tr>
        <?php
          if ($domrow['quotas'] > "0") {
        ?>
            <tr>
               <td>
                 <?php printf (_('Mailbox quota (%s Mb max)'),
                   $domrow['quotas']); ?>
                :</td>
                <td>
                  <input type="text" size="5" name="quota"
                    value="<?php echo $row['quota']; ?>"
                    class="textfield">Mb
                </td>
              </tr>
          <?php
            }
            if ((function_exists(imap_get_quotaroot))
              && ($imap_to_check_quota == "yes")) {
              $mbox = imap_open(
                $imapquotaserver, $row['username'], $row['clear'], OP_HALFOPEN
              );
              $quota = imap_get_quotaroot($mbox, "INBOX");
              if (is_array($quota)) {
              printf ("<tr><td>"
                . _('Space used:')
                . "</td><td>%.2f MB</td></tr>",
                $quota['STORAGE']['usage'] / 1024);
              }
              imap_close($mbox);
              print "</tr>";
            }
          if ($domrow['pipe'] == "1") {
          ?>
          <tr>
            <td><?php echo _('Pipe to command or alternative Maildir'); ?>:</td>
            <td>
              <input type="textfield" size="25" name="smtp" class="textfield"
                value="<?php echo $row['smtp']; ?>">
            </td>
          </tr>
          <tr>
            <td colspan="2" style="padding-bottom:1em">
              <?php echo _('Optional'); ?>:
              <?php echo _('Pipe all mail to a command (e.g. procmail).'); ?>
              <br>
              <?php echo _('Check box below to enable'); ?>:
            </td>
          </tr>
          <tr>
            <td><?php _('Enable piped command or alternative Maildir?'); ?></td>
            <td>
              <input type="checkbox" name="on_piped"
              <?php
                if ($row['on_piped'] == "1") {
                  print " checked ";
                } ?>>
            </td>
          </tr>
        <?php
          }
        ?>
        <tr>
          <td>
            <?php echo _('Admin'); ?>:</td>
            <td>
              <input name="admin" type="checkbox"<?php if ($row['admin'] == 1) { 
                print " checked";
              } ?> class="textfield">
            </td>
          </tr>
        <?php
          if ($domrow['avscan'] == "1") {
        ?>
          <tr>
            <td><?php echo _('Anti-Virus'); ?>:</td>
            <td><input name="on_avscan" type="checkbox"
              <?php if ($row['on_avscan'] == "1") {
                print " checked";
              } ?>>
            </td>
          </tr>
        <?php
           }
           if ($domrow['spamassassin'] == "1") {
        ?>
            <tr>
              <td><?php echo _('Spamassassin'); ?>:</td>
              <td><input name="on_spamassassin" type="checkbox"
                <? if ($row['on_spamassassin'] == "1") {
                  print " checked";
                }?>>
              </td>
            </tr>
            <tr>
              <td><?php echo _('Spamassassin tag score'); ?>:</td>
              <td>
                <input type="text" size="5" name="sa_tag"
                  value="<?php echo $row['sa_tag']; ?>" class="textfield">
              </td>
            </tr>
            <tr>
              <td><?php echo _('Spamassassin refuse score'); ?>:</td>
              <td>
                <input type="text" size="5" name="sa_refuse"
                  value="<?php echo $row['sa_refuse']; ?>" class="textfield">
              </td>
            </tr>
          <?php
            }
          ?>
        <tr>
          <td><?php echo _('Maximum message size'); ?>:</td>
          <td>
            <input type="text" size="5" name="maxmsgsize"
              value="<?php echo $row['maxmsgsize']; ?>" class="textfield">Kb
          </td>
        </tr>
        <tr>
          <td><?php echo _('Enabled'); ?>:</td>
          <td><input name="enabled" type="checkbox" <?php
            if ($row['enabled'] == 1) {
              print "checked";
            } ?> class="textfield">
          </td>
        </tr>
        <tr>
          <td><?php echo _('Vacation on'); ?>:</td>
          <td><input name="on_vacation" type="checkbox" <?php
            if ($row['on_vacation'] == "1") {
              print " checked ";
            } ?>>
          </td>
        </tr>
        <tr>
          <td><?php echo _('Vacation message'); ?>:</td>
          <td>
            <textarea name="vacation" cols="40" rows="5" class="textfield">
              <?php print $row['vacation']; ?>
            </textarea>
          </td>
        <tr>
          <td><?php echo _('Forwarding on'); ?>:</td>
          <td><input name="on_forward" type="checkbox" <?php
            if ($row['on_forward'] == "1") {
              print " checked";
            } ?>>
          </td>
        </tr>
        <tr>
          <td><?php echo _('Forward mail to'); ?>:</td>
          <td>
            <input type="text" size="25" name="forward"
            value="<?php print $row['forward']; ?>" class="textfield"><br>
            <? echo _('Must be a full e-mail address'); ?>!<br>
            <? echo _('OR') .":<br>\n"; ?>
            <select name="forwardmenu">
              <option selected value=""></option>
              <?php
                $queryuserlist = "SELECT realname, username, user_id, unseen
                FROM users
                WHERE enabled = '1' AND domain_id = {$_SESSION['domain_id']}
                AND type != 'fail' ORDER BY realname, username, type desc";
                $resultuserlist = $db->query($queryuserlist);
                while ($rowuserlist = $resultuserlist->fetchRow()) {
              ?>
                <option value="<?php echo $rowuserlist['username']; ?>">
                  <?php echo $rowuserlist['realname']; ?>
                  (<?php echo $rowuserlist['username']; ?>)
                </option>
              <?php 
                }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><?php echo _('Store Forwarded Mail Locally'); ?>:</td>
          <td><input name="unseen" type="checkbox" <?php
            if ($row['unseen'] == "1") {
              print " checked ";
            } ?>>
          </td>
        </tr>
        <input name="user_id" type="hidden"
          value="<?php print $_GET['user_id']; ?>" class="textfield">
        <input name="localpart" type="hidden"
          value="<?php print $row['localpart']; ?>" class="textfield">
        <tr>
          <td colspan="2" class="button">
            <input name="submit" type="submit" value="Submit">
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:1em">
          <?php echo _('Aliases to this account'); ?>:<br>
          <?php
            # Print the aliases associated with this account
            $query = "SELECT user_id,localpart,domain,realname FROM users,domains
              WHERE smtp='{$row['localpart']}@{$_SESSION['domain']}'
              AND users.domain_id=domains.domain_id ORDER BY realname";
            $result = $db->query($query);
            if ($result->numRows()) {
              while ($row = $result->fetchRow()) {
                if (($row['domain'] == $_SESSION['domain'])
                  && ($row['localpart'] != "*")) {
                  print '<a href="adminaliaschange.php?user_id='
                    . $row['user_id']
                    . '">'
                    . $row['localpart']. '@' . $row['domain']
                    . '</a>';
                } else if (($row['domain'] == $_SESSION['domain'])
                  && ($row['localpart'] == "*")) {
                  print '<a href="admincatchall.php?user_id='
                    . $row['user_id']
                    . '">'
                    . $row['localpart'] . '@' . $row['domain']
                    . '</a>';
              } else {
                print $row['localpart'] . '@' . $row['domain'];
              }
              if ($row['realname'] == "Catchall") {
                print $row[realname];
              }
              print '<br>';
            }
          }
        ?>
        </td></tr>
      </form>
    </table>
    <table align="center">
      <form name="blocklist" method="post" action="adminuserblocksubmit.php">
        <tr>
          <td colspan="2">
            <?php
              echo _('Add a new header blocking filter for this user');
            ?>:
          </td>
        </tr>
        <tr>
          <td><?php echo _('Header'); ?>:</td>
          <td>
            <select name="blockhdr" class="textfield">
              <option value="From"><?php echo _('From'); ?>:</option>
              <option value="To"><?php echo _('To'); ?>:</option>
              <option value="Subject"><?php echo _('Subject'); ?>:</option>
              <option value="X-Mailer"><?php echo _('X-Mailer'); ?>:</option>
            </select>
          </td>
          <td>
            <input name="blockval" type="text" size="25" class="textfield">
            <input name="user_id" type="hidden"
              value="<?php print $_GET['user_id']; ?>">
            <input name="localpart" type="hidden"
              value="<?php print $_GET['localpart']; ?>">
            <input name="color" type="hidden" value="black">
          </td>
        </tr>
        <tr>
          <td>
            <input name="submit" type="submit"
              value="<?php echo _('Submit'); ?>">
          </td>
        </tr>
      </form>
    </table>
    <table align="center">
      <tr>
        <th><?php echo _('Delete'); ?></th>
        <th><?php echo _('Blocked Header'); ?></th>
        <th><?php echo _('Content'); ?></th>
      </tr>
      <?php
        if ($blockresult->numRows()) {
          while ($blockrow = $blockresult->fetchRow()) {
      ?>
            <tr>
              <td>
                <a href="adminuserblocksubmit.php?action=delete&user_id=
                  <?php echo $_GET['user_id']; ?>&block_id=
                  <?php echo $blockrow['block_id']; ?>&localpart=
                  <?php $_GET['localpart']; ?>">
                  <img class="trash" title="Delete" src="images/trashcan.gif"
                    alt="trashcan">
                </a>
              </td>
              <td><?php echo $blockrow['blockhdr']; ?></td>
              <td><?php echo $blockrow['blockval']; ?></td>
            </tr>
        <?php
          }
        }
      ?>
    </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
