<?php

/**
 * @file
 * Contains \Drupal\pathauto\Tests\PathautoUserWebTest.
 */

namespace Drupal\pathauto\Tests;
use Drupal\Component\Utility\Unicode;
use Drupal\simpletest\WebTestBase;
use Drupal\views\Views;

/**
 * Tests pathauto user UI integration.
 *
 * @group pathauto
 */
class PathautoUserWebTest extends WebTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('pathauto', 'views');

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {inheritdoc}
   */
  function setUp() {
    parent::setUp();

    // Allow other modules to add additional permissions for the admin user.
    $permissions = array(
      'administer pathauto',
      'administer url aliases',
      'create url aliases',
      'administer users',
    );
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);
  }


  /**
   * Basic functional testing of Pathauto with users.
   */
  function testUserEditing() {
    // There should be no Pathauto checkbox on user forms.
    $this->drupalGet('user/' . $this->adminUser->id() . '/edit');
    $this->assertNoFieldById('path[0][pathauto]');
  }

  /**
   * Test user operations.
   */
  function testUserOperations() {
    $account = $this->drupalCreateUser();

    // Delete all current URL aliases.
    $this->deleteAllAliases();

    // Find the position of just created account in the user_admin_people view.
    $view = Views::getView('user_admin_people');
    $view->initDisplay();
    $view->preview('page_1');


    foreach ($view->result as $key => $row) {
      if ($view->field['name']->getValue($row) == $account->getUsername()) {
        break;
      }
    }

    $edit = array(
      'action' => 'pathauto_update_alias_user',
      "user_bulk_form[$key]" => TRUE,
    );
    $this->drupalPostForm('admin/people', $edit, t('Apply'));
    $this->assertRaw(\Drupal::translation()->formatPlural(1, '%action was applied to @count item.', '%action was applied to @count items.', array(
      '%action' => 'Update URL-Alias',
    )));

    $this->assertEntityAlias($account, '/users/' . Unicode::strtolower($account->getUsername()));
    $this->assertEntityAlias($this->adminUser, '/user/' . $this->adminUser->id());
  }

}
