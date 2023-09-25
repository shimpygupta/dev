<?php

namespace Drupal\custom_timezone;

use Drupal\Core\Datetime\DateFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to provide current time in a specific format.
 */
class CustomTimeService {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * CustomTimeService constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(DateFormatterInterface $dateFormatter) {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Factory method to create an instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter')
    );
  }

  /**
   * Get the current time in the specified timezone.
   *
   * @param string $timezone
   *   The timezone to use for formatting the time.
   *
   * @return string
   *   The formatted time string.
   */
  public function getCurrentTime($timezone) {

    // Get the current timestamp for the selected timezone in Admin Config Form.
    $current_time = \Drupal::time()->getCurrentTime();
    $formatted_date = $this->dateFormatter->format($current_time, 'custom', 'l j F Y', $timezone);
    $formatted_time = $this->dateFormatter->format($current_time, 'custom', 'g:i A', $timezone);

  return [
    'formatted_date' => $formatted_date,
    'formatted_time' => $formatted_time,
  ];
}
}
