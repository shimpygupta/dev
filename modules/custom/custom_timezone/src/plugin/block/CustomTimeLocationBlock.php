<?php

namespace Drupal\custom_timezone\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\custom_timezone\CustomTimeService;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a block that displays location and current time.
 *
 * @Block(
 *   id = "custom_timezone_location_time_block",
 *   admin_label = @Translation("Location and Current Time Block"),
 * )
 */
class CustomTimeLocationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The custom time service.
   *
   * @var \Drupal\custom_timezone\CustomTimeService
   */
  protected $customTimeService;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CustomTimeLocationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\custom_timezone\CustomTimeService $customTimeService
   *   The custom time service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CustomTimeService $customTimeService, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->customTimeService = $customTimeService;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('custom_timezone.time_service'),
      $container->get('config.factory')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the location from the Admin configuration form.
    $config = $this->configFactory->get('custom_timezone.settings');
    $country = $config->get('country');
    $city= $config->get('city'); 

    // Get the current time based on the selected timezone in Admin configuration Form.
    $timezone = $config->get('timezone');
    $current_time = $this->customTimeService->getCurrentTime($timezone);
    
    // Define the cache tags.
  $cache_tags = [
    'config:custom_timezone.settings',
  ];
  print_r($cache_tags);
  // Define the cache contexts.
  $cache_contexts = [
    'timezone',
  ];
  print_r($cache_contexts);
    $content=[
        '#markup' => '<div class="custom-time" style="font-weight:bold;">' . 
        t('@formatted_time<br>@formatted_date', [
          '@formatted_date' => $current_time['formatted_date'],
          '@formatted_time' => $current_time['formatted_time'],
        ]),
        
        // Add cache tags and contexts for dynamic caching.
    '#cache' => [
      'max-age' => 3600,
      'tags' => $cache_tags,
      'contexts' => $cache_contexts,
    ],
  ];
     $content['#markup'].= '<div style="current-location">Time in ' . $city. ','.$country. ' '.'</div>';
     return $content;
     
     /// Define the variables to pass to the Twig template.
  $variables = [
    'city' => $city,
    'country' => $country,
    'formatted_time' => $current_time['formatted_time'],
    'formatted_date' => $current_time['formatted_date'],
  ];
  
  // Build the render array with the theme hook and variables.
  $return = [
    '#theme' => 'custom_timezone_location_time_block',
    '#variables' => $variables,
    '#cache' => [
      'max-age' => 3600,
      'tags' => $cache_tags,
      'contexts' => $cache_contexts,
    ],
  ];
  }
}
