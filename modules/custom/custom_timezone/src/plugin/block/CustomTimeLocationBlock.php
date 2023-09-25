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
  public function getCacheTags() {
    // Cache tags for the data your block depends on.
    $cache_tags = [
      'config:custom_timezone.settings', 
    ];

    return $cache_tags;
  }
   
  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Add cache contexts for the conditions your block should vary by.
    $cache_contexts = [
      'timezone',
    ];

    return $cache_contexts;
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
    
    $content1=[
        '#markup' => '<div class="custom-time" style="font-weight:bold;">' . 
        t('@formatted_time<br>@formatted_date', [
          '@formatted_date' => $current_time['formatted_date'],
          '@formatted_time' => $current_time['formatted_time'],
        ]),
          // Add cache tags and contexts for dynamic caching.
          $content1['#cache']['max-age'] = ['3600'],
          $content1['#cache']['tags'] = ['custom_time_block:time'],
          $content1['#cache']['contexts'] = ['timezone'],
        '#cache' => [
          'max-age' => 10, 
          // 'cache_tags' => $cache_tags,
          // 'cache_contexts' => $cache_contexts,
        ],
    ];
     $content1['#markup'].= '<div style="current-location">Time in ' . $city. ','.$country. ' '.'</div>';
     return $content1;

     /// Define the variables to pass to the Twig template.
  $variables = [
    'city' => $city,
    'country' => $country,
  ];

  // Build the render array with the theme hook and variables.
  $content = [
    '#theme' => 'custom_timezone_location_time_block',
    '#variables' => $variables,
  ];

  return $content;
  }
}
