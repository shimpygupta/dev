<?php

namespace Drupal\custom_timezone\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_timezone\CustomTimeService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for configuring custom timezone settings.
 */
class CustomTimezoneForm extends ConfigFormBase {
  
  protected $customTimeService;

  public function __construct(ConfigFactoryInterface $config_factory, CustomTimeService $customTimeService) {
    parent::__construct($config_factory);
    $this->customTimeService = $customTimeService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('custom_timezone.time_service')
    );
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_timezone.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_timezone_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_timezone.settings');

    $form['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#default_value' => $config->get('country'),
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#default_value' => $config->get('city'),
    ];

    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone'),
      '#options' => [
        'America/Chicago' => 'America/Chicago',
        'America/New_York' => 'America/New_York',
        'Asia/Tokyo' => 'Asia/Tokyo',
        'Asia/Dubai' => 'Asia/Dubai',
        'Asia/Kolkata' => 'Asia/Kolkata',
        'Europe/Amsterdam' => 'Europe/Amsterdam',
        'Europe/Oslo' => 'Europe/Oslo',
        'Europe/London' => 'Europe/London',
      ],
      '#default_value' => $config->get('timezone'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('custom_timezone.settings');

    // Get the previous timezone value from the configuration.
    $prev_timezone = $config->get('timezone');

    $config->set('country', $form_state->getValue('country'))
      ->set('city', $form_state->getValue('city'))
      ->set('timezone', $form_state->getValue('timezone'))
      ->save();

    // Check if the timezone value has changed.
     $new_timezone = $form_state->getValue('timezone');
     if ($prev_timezone !== $new_timezone) {

     // Invalidate cache tags associated with your block when the timezone changes.
    \Drupal\Core\Cache\Cache::invalidateTags(['custom_timezone_settings']);
  }


    parent::submitForm($form, $form_state);
  }
}
