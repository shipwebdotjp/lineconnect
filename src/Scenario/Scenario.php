<?php

/**
 * シナリオ
 */

namespace Shipweb\LineConnect\Scenario;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Message\LINE\Sender;
use Shipweb\LineConnect\Utilities\Condition;
use stdClass;

/**
 * シナリオクラス
 */
class Scenario {
	/**
	 * CredentialAction
	 */
	const NAME = 'scenario';

	/**
	 * CredentialAction
	 */
	const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;

	/**
	 * CredentialName
	 */
	const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;

	/**
	 * 投稿メタキー
	 */
	const META_KEY_DATA = self::NAME . '-data';

	/**
	 * パラメータ名
	 */
	const PARAMETER_DATA = LineConnect::PLUGIN_PREFIX . self::META_KEY_DATA;

	/**
	 * Schema Version
	 */
	const SCHEMA_VERSION = 1;

	/**
	 * カスタム投稿タイプスラッグ
	 */
	const POST_TYPE = lineconnect::PLUGIN_PREFIX . self::NAME;
	/**
	 * シナリオ開始フラグ
	 */
	const START_FLAG_RESTART_NONE = 'none';            // 未実行の場合のみ開始
	const START_FLAG_RESTART_COMPLETED = 'completed';  // 完了済みの場合は最初から
	const START_FLAG_RESTART_ALWAYS = 'always';          // どんな場合でも最初から
	/**
	 * シナリオステータス
	 * 
	 * @var string
	 */
	const STATUS_NONE = 'none';            // 未実行
	const STATUS_ACTIVE = 'active';
	const STATUS_COMPLETED = 'completed';
	const STATUS_ERROR = 'error';
	const STATUS_PAUSED = 'paused';

	/**
	 * スキーマを返す
	 * 
	 * @return array JSONスキーマ
	 */
	public static function getSchema() {
		$step_schema = array(
			'type' => 'array',
			'title' => __('Steps', lineconnect::PLUGIN_NAME),
			'description' => __('Steps', lineconnect::PLUGIN_NAME),
			'items' => array(
				'type' => 'object',
				'title' => __('Step', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'id' => array(
						'type' => 'string',
						'title' => __('ID', lineconnect::PLUGIN_NAME),
						'description' => __('Step ID', lineconnect::PLUGIN_NAME),
					),
					'condition' => array(
						'$ref' => '#/definitions/condition',
					),
					'actions' => array(
						'type' => 'array',
						'title' => __('Actions', lineconnect::PLUGIN_NAME),
						'description' => __('Actions', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type' => 'object',
							'oneOf'    => array(),
							'required' => array(
								'parameters',
							),
						),
					),
					'chains' => array(
						'type' => 'array',
						'title' => __('Action chain', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type'     => 'object',
							'properties' => array(
								'to' => array(
									'type' => 'string',
									'title' => __('Destination argument to', lineconnect::PLUGIN_NAME),
									'description' => __('Injection Destination Argument Path. e.g. 2.message', lineconnect::PLUGIN_NAME),
								),
								'data' => array(
									'type' => 'string',
									'title' => __('Data', lineconnect::PLUGIN_NAME),
									'description' => __('Injection Data. You can use return value of previous action. e.g. {{$.return.1}}', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
					'stop' => array(
						'type' => 'boolean',
						'title' => __('Finish scenario execution', lineconnect::PLUGIN_NAME),
					),
					'next' => array(
						'type' => 'string',
						'title' => __('Next step', lineconnect::PLUGIN_NAME),
						'description' => __('Next step ID. If empty, go to the step located directly below.', lineconnect::PLUGIN_NAME),
					),
					'schedule' => array(
						'type' => 'object',
						'title' => __('Schedule Configuration', lineconnect::PLUGIN_NAME),
						'description' => __('Defines the interval value, its unit, and the execution timing after the interval elapsed.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'absolute' => array(
								'type' => 'string',
								'format' => 'date-time',
								'title' => __('Absolute Date Time', lineconnect::PLUGIN_NAME),
								'description' => __('The absolute date time to execute next step.', lineconnect::PLUGIN_NAME),
							),
							'relative' => array(
								'type' => 'integer',
								'title' => __('Relative Interval Value', lineconnect::PLUGIN_NAME),
								'description' => __('The numerical value of the relative interval.', lineconnect::PLUGIN_NAME),
								'default' => 1,
							),
							'unit' => array(
								'type' => 'string',
								'title' => __('Interval Unit', lineconnect::PLUGIN_NAME),
								'description' => __('The unit of time for the interval (e.g., minutes, hours, days).', lineconnect::PLUGIN_NAME),
								'oneOf' => array(
									array(
										'const' => 'minutes',
										'title' => __('Minutes', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'hours',
										'title' => __('Hours', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'days',
										'title' => __('Days', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'weeks',
										'title' => __('Weeks', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'months',
										'title' => __('Months', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'years',
										'title' => __('Years', lineconnect::PLUGIN_NAME),
									),
								),
								'default' => 'days',
							),
						),
						'dependencies' => array(
							'unit' => array(
								'oneOf' => array(
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'minutes',
											),
										),
									),
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'hours',
											),
											'type' => array(
												'type'        => 'string',
												'title'       => __('Execution Timing', lineconnect::PLUGIN_NAME),
												'description' => __('Choose whether to execute the step immediately after the exact interval elapses or to align the execution to a specified minute marker within the next hour.', lineconnect::PLUGIN_NAME),
												'oneOf'       => array(
													array(
														'const'       => 'exact',
														'title'       => __('Exact Interval Execution', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes precisely after the full interval has elapsed (for example, if the interval is 1 hour, it runs exactly 1 hour later).', lineconnect::PLUGIN_NAME),
													),
													array(
														'const'       => 'base',
														'title'       => __('Align to Minute Marker', lineconnect::PLUGIN_NAME),
														'description' => __('After advancing by the specified number of hours, the step executes at the designated minute mark (for example, if set to minute 05, it executes at HH:05).', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'exact',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'base',
															),
															'minute' => array(
																'type'        => 'integer',
																'minimum'     => 0,
																'maximum'     => 59,
																'title'       => __('Minute', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the minute within the hour (e.g., 5 for HH:05).', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
										),
									),
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'days',
											),
											'type' => array(
												'type' => 'string',
												'title' => __('Execution Timing', lineconnect::PLUGIN_NAME),
												'description' => __('Choose whether to execute the step immediately after the exact interval elapses or to align the execution to a specific time on subsequent days.', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'exact',
														'title' => __('Exact Interval Execution', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes precisely after the full interval has elapsed (for example, if the interval is 2 days, it runs exactly 48 hours later).', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'base',
														'title' => __('Specific Daily Time', lineconnect::PLUGIN_NAME),
														'description' => __('After advancing by the specified number of days, the step executes at the designated time (e.g., 14:30 would trigger at 2:30 PM on the target day).', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'exact',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'base',
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
										),
									),
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'weeks',
											),
											'type' => array(
												'type'        => 'string',
												'title'       => __('Execution Timing', lineconnect::PLUGIN_NAME),
												'description' => __('Choose whether to execute the step at a specific time within the week or to align execution to a specific weekday and time.', lineconnect::PLUGIN_NAME),
												'oneOf'       => array(
													array(
														'const'       => 'exact',
														'title'       => __('Exact Time Execution', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes at a specified time within the week (e.g., 12:10 on any day of the week).', lineconnect::PLUGIN_NAME),
													),
													array(
														'const'       => 'base',
														'title'       => __('Align to Weekday and Time', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes on a specified weekday at a specified time (e.g., Saturday at 12:10).', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'exact',
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'base',
															),
															'weekday' => array(
																'type' => 'integer',
																'oneOf' => array(
																	array(
																		'const' => 0,
																		'title' => __('Sunday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 1,
																		'title' => __('Monday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 2,
																		'title' => __('Tuesday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 3,
																		'title' => __('Wednesday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 4,
																		'title' => __('Thursday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 5,
																		'title' => __('Friday', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 6,
																		'title' => __('Saturday', lineconnect::PLUGIN_NAME),
																	),
																),
																'title' => __('Day of the week', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the day of the week (e.g., Saturday).', lineconnect::PLUGIN_NAME),
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
										),
									),
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'months',
											),
											'type' => array(
												'type'        => 'string',
												'title'       => __('Execution Timing', lineconnect::PLUGIN_NAME),
												'description' => __('Choose whether to execute the step at a specific time within the month or to align execution to a specific day and time.', lineconnect::PLUGIN_NAME),
												'oneOf'       => array(
													array(
														'const'       => 'exact',
														'title'       => __('Exact Time Execution', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes at a specified time within the month (e.g., 12:10 on any day of the month).', lineconnect::PLUGIN_NAME),
													),
													array(
														'const'       => 'base',
														'title'       => __('Align to Day and Time', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes on a specified day of the month at a specified time (e.g., the 15th at 12:10).', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'exact',
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'base',
															),
															'day' => array(
																'type' => 'integer',
																'oneOf' => array(
																	array(
																		'const' => 1,
																		'title' => __('1st', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 2,
																		'title' => __('2nd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 3,
																		'title' => __('3rd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 4,
																		'title' => __('4th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 5,
																		'title' => __('5th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 6,
																		'title' => __('6th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 7,
																		'title' => __('7th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 8,
																		'title' => __('8th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 9,
																		'title' => __('9th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 10,
																		'title' => __('10th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 11,
																		'title' => __('11th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 12,
																		'title' => __('12th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 13,
																		'title' => __('13th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 14,
																		'title' => __('14th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 15,
																		'title' => __('15th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 16,
																		'title' => __('16th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 17,
																		'title' => __('17th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 18,
																		'title' => __('18th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 19,
																		'title' => __('19th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 20,
																		'title' => __('20th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 21,
																		'title' => __('21st', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 22,
																		'title' => __('22nd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 23,
																		'title' => __('23rd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 24,
																		'title' => __('24th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 25,
																		'title' => __('25th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 26,
																		'title' => __('26th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 27,
																		'title' => __('27th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 28,
																		'title' => __('28th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 29,
																		'title' => __('29th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 30,
																		'title' => __('30th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 31,
																		'title' => __('31st', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 0,
																		'title' => __('Last day of the month', lineconnect::PLUGIN_NAME),
																	),
																),
																'title' => __('Day of the month', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the day of the month (e.g., 15).', lineconnect::PLUGIN_NAME),
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
										),
									),
									array(
										'properties' => array(
											'unit' => array(
												'const' => 'years',
											),
											'type' => array(
												'type'        => 'string',
												'title'       => __('Execution Timing', lineconnect::PLUGIN_NAME),
												'description' => __('Choose whether to execute the step at a specific time within the year or to align execution to a specific month, day, and time.', lineconnect::PLUGIN_NAME),
												'oneOf'       => array(
													array(
														'const'       => 'exact',
														'title'       => __('Exact Time Execution', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes at a specified time within the year (e.g., 12:10 on any date).', lineconnect::PLUGIN_NAME),
													),
													array(
														'const'       => 'base',
														'title'       => __('Align to Month, Day, and Time', lineconnect::PLUGIN_NAME),
														'description' => __('The step executes on a specified month and day at a specified time (e.g., June 15th at 12:10).', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'exact',
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'base',
															),
															'month' => array(
																'type' => 'integer',
																'oneOf' => array(
																	array(
																		'const' => 1,
																		'title' => __('January', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 2,
																		'title' => __('February', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 3,
																		'title' => __('March', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 4,
																		'title' => __('April', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 5,
																		'title' => __('May', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 6,
																		'title' => __('June', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 7,
																		'title' => __('July', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 8,
																		'title' => __('August', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 9,
																		'title' => __('September', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 10,
																		'title' => __('October', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 11,
																		'title' => __('November', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 12,
																		'title' => __('December', lineconnect::PLUGIN_NAME),
																	),
																),
																'title' => __('Month of the year', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the month of the year (1-12).', lineconnect::PLUGIN_NAME),
															),
															'day' => array(
																'type' => 'integer',
																'oneOf' => array(
																	array(
																		'const' => 1,
																		'title' => __('1st', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 2,
																		'title' => __('2nd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 3,
																		'title' => __('3rd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 4,
																		'title' => __('4th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 5,
																		'title' => __('5th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 6,
																		'title' => __('6th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 7,
																		'title' => __('7th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 8,
																		'title' => __('8th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 9,
																		'title' => __('9th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 10,
																		'title' => __('10th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 11,
																		'title' => __('11th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 12,
																		'title' => __('12th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 13,
																		'title' => __('13th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 14,
																		'title' => __('14th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 15,
																		'title' => __('15th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 16,
																		'title' => __('16th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 17,
																		'title' => __('17th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 18,
																		'title' => __('18th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 19,
																		'title' => __('19th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 20,
																		'title' => __('20th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 21,
																		'title' => __('21st', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 22,
																		'title' => __('22nd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 23,
																		'title' => __('23rd', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 24,
																		'title' => __('24th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 25,
																		'title' => __('25th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 26,
																		'title' => __('26th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 27,
																		'title' => __('27th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 28,
																		'title' => __('28th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 29,
																		'title' => __('29th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 30,
																		'title' => __('30th', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 31,
																		'title' => __('31st', lineconnect::PLUGIN_NAME),
																	),
																),
																'title' => __('Day of the month', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the day of the month (e.g., 15).', lineconnect::PLUGIN_NAME),
															),
															'time' => array(
																'type'        => 'string',
																'format'     => 'time',
																'title'       => __('Time (HH:mm)', lineconnect::PLUGIN_NAME),
																'description' => __('Specify the time of day in HH:mm format (e.g., 12:10).', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			),
			'definitions' => array(
				'condition' => array(
					'type'  => 'object',
					'title' => __('Destination condition', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Destination condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Destination condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' =>  __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'channel',
												'title' => __('Channel', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'destination',
												'title' => __('Destination type', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'link',
												'title' => __('Link status', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'role',
												'title' => __('Role', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'usermeta',
												'title' => __('User meta', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'profile',
												'title' => __('Profile', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Destination condition group', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'not' => array(
										'type' => 'boolean',
										'title' => __('Not', lineconnect::PLUGIN_NAME),
										'description' => __('Logical negation', lineconnect::PLUGIN_NAME),
									),
								),
								'dependencies' => array(
									'type' => array(
										'oneOf' => array(
											array(
												'properties' => array(
													'type' => array(
														'const' => 'channel',
													),
													'secret_prefix' => array(
														'$ref' => '#/definitions/secret_prefix',
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'destination',
													),
													'destination' => array(
														'type' => 'object',
														'title' => __('Destination', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'user',
																		'title' => __('User', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'group',
																		'title' => __('Group', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'room',
																		'title' => __('Room', lineconnect::PLUGIN_NAME),
																	),
																),
															),
														),
														'dependencies' => array(
															'type' => array(
																'oneOf' => array(
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'user',
																			),
																			'lineUserId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
																				'minItems' => 1,
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'group',
																			),
																			'groupId' => array(
																				'type' => 'array',
																				'title' => __('LINE group ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'room',
																			),
																			'roomId' => array(
																				'type' => 'array',
																				'title' => __('LINE Room ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'link',
													),
													'link' => array(
														'type' => 'string',
														'title' => __('Link status', lineconnect::PLUGIN_NAME),
														'anyOf' => array(
															array(
																'const' => 'any',
																'title' => __('Any', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'linked',
																'title' => __('Linked', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'unlinked',
																'title' => __('Unlinked', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'role',
													),
													'role' => array(
														'$ref' => '#/definitions/role',
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'usermeta',
													),
													'usermeta' => array(
														'type' => 'array',
														'title' => __('User Meta', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'object',
															'required' => ['key', 'value', 'compare'],
															'properties' => array(
																'key' => array(
																	'type' => 'string',
																	'title' => __('Meta Key', lineconnect::PLUGIN_NAME),
																),
																'compare' => array(
																	'$ref' => '#/definitions/compare',
																),
															),
															'dependencies' => array(
																'compare' => array(
																	'oneOf' => array(
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'IN',
																						'NOT IN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 1,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'BETWEEN',
																						'NOT BETWEEN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 2,
																					'maxItems' => 2,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'=',
																						'!=',
																						'>',
																						'>=',
																						'<',
																						'<=',
																						'LIKE',
																						'NOT LIKE',
																						'REGEXP',
																						'NOT REGEXP',
																					),
																				),
																				'value' => array(
																					'type' => 'string',
																					'title' => __('Value', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'EXISTS',
																						'NOT EXISTS',
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'profile',
													),
													'profile' => array(
														'type' => 'array',
														'title' => __('Profile data', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'object',
															'required' => ['key', 'value', 'compare'],
															'properties' => array(
																'key' => array(
																	'type' => 'string',
																	'title' => __('Profile field', lineconnect::PLUGIN_NAME),
																),
																'compare' => array(
																	'$ref' => '#/definitions/compare',
																),
															),
															'dependencies' => array(
																'compare' => array(
																	'oneOf' => array(
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'IN',
																						'NOT IN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 1,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'BETWEEN',
																						'NOT BETWEEN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 2,
																					'maxItems' => 2,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'=',
																						'!=',
																						'>',
																						'>=',
																						'<',
																						'<=',
																						'LIKE',
																						'NOT LIKE',
																						'REGEXP',
																						'NOT REGEXP',
																					),
																				),
																				'value' => array(
																					'type' => 'string',
																					'title' => __('Value', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'EXISTS',
																						'NOT EXISTS',
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/condition',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type'  => 'string',
							'title' => __('Operator', lineconnect::PLUGIN_NAME),
							'oneOf' => array(
								array(
									'const' => 'and',
									'title' => __('And', lineconnect::PLUGIN_NAME),
									'description' => __('All conditions must be true', lineconnect::PLUGIN_NAME),
								),
								array(
									'const' => 'or',
									'title' => __('Or', lineconnect::PLUGIN_NAME),
									'description' => __('At least one condition must be true', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
				),
				'role' => array(
					'type' => 'array',
					'title' => __('Role', lineconnect::PLUGIN_NAME),
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
					'uniqueItems' => true,
				),
				'secret_prefix' => array(
					'type' => 'array',
					'title' => __('Channel', lineconnect::PLUGIN_NAME),
					'description' => __('Target channel', lineconnect::PLUGIN_NAME),
					'uniqueItems' => true,
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
				),
				'compare' => array(
					'type' => 'string',
					'title' => __('Compare method', lineconnect::PLUGIN_NAME),
					'default' => '=',
					'anyOf' => array(
						array(
							'const' => '=',
							'title' => __('Equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '!=',
							'title' => __('Not equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>',
							'title' => __('Greater than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>=',
							'title' => __('Greater than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<',
							'title' => __('Less than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<=',
							'title' => __('Less than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'LIKE',
							'title' => __('Contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT LIKE',
							'title' => __('Not contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'IN',
							'title' => __('In (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT IN',
							'title' => __('Not in (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'BETWEEN',
							'title' => __('Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT BETWEEN',
							'title' => __('Not Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'EXISTS',
							'title' => __('Exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT EXISTS',
							'title' => __('Not exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'REGEXP',
							'title' => __('Regular expression match', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT REGEXP',
							'title' => __('No regular expression match', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'schedule_type' => array(
					'type' => 'string',
					'title' => __('Execution Timing', lineconnect::PLUGIN_NAME),
					'description' => __('Determines whether the step is executed exactly after the interval or at the next standard time reference.', lineconnect::PLUGIN_NAME),
					'oneOf' => array(
						array(
							'const' => 'exact',
							'title' => __('Exact After Interval', lineconnect::PLUGIN_NAME),
							'description' => __('Executes the step immediately after the specified interval has elapsed.', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'base',
							'title' => __('Base Time Reference', lineconnect::PLUGIN_NAME),
							'description' => __('Executes the step at the next base time reference (e.g., the next hour, day, etc.).', lineconnect::PLUGIN_NAME),
						),
					),
				),
			),
		);
		Action::build_action_schema_items($step_schema['items']['properties']['actions']['items']['oneOf']);
		/*
		$action_array   = Action::get_lineconnect_action_data_array();
		if (!empty($action_array)) {
			foreach ($action_array as $name => $action) {
				$properties = array(
					'action_name' => array(
						'type'    => 'string',
						'const'   => $name,
						'default' => $name,
					),
					'response_return_value' => array(
						'type'	=> 'boolean',
						'default' => true,
						'title' => __('Send the return value as a response', lineconnect::PLUGIN_NAME),
						'description' => __('Send the return value of this action as a response message by LINE message', lineconnect::PLUGIN_NAME),
					),
				);
				if (isset($action['parameters'])) {
					$parameters            = $action['parameters']; //['properties']
					$parameters_properties = array();
					if (! empty($parameters)) {
						foreach ($parameters as $idx => $parameter) {
							$key                           = $parameter['name'] ?? 'param' . $idx;
							$val                           = lineconnectUtil::get_parameter_schema($key, $parameter);
							$parameters_properties[$key] = $val;
						}
					}
					if (! empty($parameters_properties)) {
						$properties['parameters'] = array(
							'type'       => 'object',
							'title'      => __('Parameters', lineconnect::PLUGIN_NAME),
							'properties' => $parameters_properties,
						);
					}
				}
				$step_schema['items']['properties']['actions']['items']['oneOf'][] = array( // ['properties']['parameters']
					'title'      => $action['title'],
					'properties' => $properties,
					'required'   => array('action_name'),
				);
			}
		} else {
			$step_schema['items']['properties']['actions']['items']['oneOf'] = array(
				array(
					'title'      => __('Please add action first', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'action_name' => array(
							'type'    => 'null',
						),
					),
				),
			);
		}
		*/
		$all_roles = array();
		foreach (wp_roles()->roles as $role_name => $role) {
			$all_roles[] = array(
				'const' => esc_attr($role_name),
				'title' => translate_user_role($role['name']),
			);
		}
		$step_schema['definitions']['role']['items']['oneOf'] = $all_roles;

		$all_channels = array();
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$all_channels[] = array(
				'const' => $channel['prefix'],
				'title' => $channel['name'],
			);
		}
		if (count($all_channels) == 0) {
			$all_channels[] = array(
				'const' => '',
				'title' => __('Please add channel first', lineconnect::PLUGIN_NAME),
			);
		}
		$step_schema['definitions']['secret_prefix']['items']['oneOf'] = $all_channels;

		$schema = apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . self::NAME . '_schema', $step_schema);
		return $schema;
	}

	/**
	 * UIスキーマを返す
	 * 
	 * @return array JSONスキーマ
	 */
	public static function getUiSchema() {
		$uiSchema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'items' => array(
				'condition' => array(
					'conditions' => array(
						'items' => array(
							'ui:order' => array(
								'type',
								'secret_prefix',
								'destination',
								'link',
								'role',
								'usermeta',
								'profile',
								'group',
								'*',
							),
							'destination' => array(
								'lineUserId' => array(
									'ui:options' => array(
										'addText' => __('Add LINE user ID', lineconnect::PLUGIN_NAME),
									),
								),
								'groupId' => array(
									'ui:options' => array(
										'addText' => __('Add LINE group ID', lineconnect::PLUGIN_NAME),
									),
								),
								'roomId' => array(
									'ui:options' => array(
										'addText' => __('Add LINE Room ID', lineconnect::PLUGIN_NAME),
									),
								),
							),
							'usermeta' => array(
								'items' => array(
									'ui:order' => array(
										'key',
										'compare',
										'*',
									),
									'values' => array(
										'ui:options' => array(
											'addText' => __('Add value', lineconnect::PLUGIN_NAME),
										),
									),
								),
								'ui:options' => array(
									'addText' => __('Add user meta', lineconnect::PLUGIN_NAME),
								),
							),
							'profile' => array(
								'items' => array(
									'ui:order' => array(
										'key',
										'compare',
										'*',
									),
									'values' => array(
										'ui:options' => array(
											'addText' => __('Add value', lineconnect::PLUGIN_NAME),
										),
									),
								),
								'ui:options' => array(
									'addText' => __('Add profile', lineconnect::PLUGIN_NAME),
								),
							),
						),
						'ui:options' => array(
							'addText' => __('Add condition', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'actions' => array(
					'items' => array(
						'action_name' => array(
							'ui:widget' => 'hidden',
						),
						'parameters' => array(
							'ui:options' => array(
								'addText' => __('Add parameter', lineconnect::PLUGIN_NAME),
							),
							'body' => array(
								'ui:widget' => 'textarea',
								'ui:options' => array(
									'rows' => 5,
								),
							),
							'json' => array(
								'ui:widget' => 'textarea',
								'ui:options' => array(
									'rows' => 5,
								),
							),
						),
					),
					'ui:options' => array(
						'addText' => __('Add action', lineconnect::PLUGIN_NAME),
					),
				),
				'chains' => array(
					'ui:options' => array(
						'addText' => __('Add chain', lineconnect::PLUGIN_NAME),
					),
				),
			),
			'ui:options' => array(
				'addText' =>  __('Add step', lineconnect::PLUGIN_NAME),
			),
		);

		return apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . self::NAME . '_uischema', $uiSchema);
	}

	/**
	 * シナリオを開始する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param ?string $flag 開始フラグ none:未実行の場合のみ開始, completed:完了済みの場合は最初から, always:どんな場合でも最初から
	 * @param ?string $line_user_id LINEユーザーID 
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 
	 */
	public static function start_scenario(int $scenario_id, ?string $flag = 'none', ?string $line_user_id = null, ?string $secret_prefix = null): array {

		// 現在のステータスを取得
		$status = self::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
		if (is_null($flag)) {
			$flag = self::START_FLAG_RESTART_NONE;
		}
		// フラグに応じた処理
		if (!empty($status)) {
			switch ($flag) {
				case self::START_FLAG_RESTART_NONE:

					// 未実行の場合のみ開始
					return ['result' => 'skip', 'message' => __('Scenario already started', lineconnect::PLUGIN_NAME)];

				case self::START_FLAG_RESTART_COMPLETED:
					// 完了済みの場合は最初から、実行中は開始しない
					if ($status['status'] === self::STATUS_ACTIVE) {
						return ['result' => 'skip', 'message' => __('Scenario is currently active', lineconnect::PLUGIN_NAME)];
					}
					if ($status['status'] !== self::STATUS_COMPLETED) {
						return ['result' => 'skip', 'message' => __('Scenario is not completed', lineconnect::PLUGIN_NAME)];
					}
					break;

				case self::START_FLAG_RESTART_ALWAYS:
					// どんな場合でも最初から
					break;

				default:
					return ['result' => 'error', 'message' => __('Invalid flag provided', lineconnect::PLUGIN_NAME)];
			}
		}

		// シナリオを開始
		$result = self::update_scenario_status($scenario_id, self::STATUS_ACTIVE, $line_user_id, $secret_prefix, null, ['next', 'next_date']);
		if ($result) {
			return self::execute_step($scenario_id, null, $line_user_id, $secret_prefix);
		}

		return ['result' => 'error', 'message' => __('Failed to start scenario', lineconnect::PLUGIN_NAME)];
	}

	/**
	 * LINEユーザーのシナリオの購読状態を取得する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array|null シナリオの購読状態またはnull
	 */
	public static function get_scenario_status(int $scenario_id, string $line_user_id, string $secret_prefix): ?array {
		global $wpdb;
		$channel_prefix = $secret_prefix;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$scenario = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT scenarios FROM $table_name WHERE line_id = %s AND channel_prefix = %s",
				$line_user_id,
				$channel_prefix
			)
		);
		if (empty($scenario)) {
			return null;
		}
		$scenarios_array = json_decode($scenario, true) ?: [];


		if (!array_key_exists($scenario_id, $scenarios_array)) {
			return null;
		}

		return $scenarios_array[$scenario_id];
	}


	/**
	 * LINEユーザーのシナリオの購読状態を更新する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param string $status ステータス
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	public static function update_scenario_status(int $scenario_id, string $status, string $line_user_id, string $secret_prefix, ?array $addtional = null, ?array $unsets = null): bool {
		global $wpdb;

		// status check
		if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_COMPLETED, self::STATUS_ERROR, self::STATUS_PAUSED])) {
			return false;
		}

		$channel_prefix = $secret_prefix;
		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$scenario = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT scenarios FROM $table_name WHERE line_id = %s AND channel_prefix = %s",
				$line_user_id,
				$channel_prefix
			)
		);
		if (!empty($scenario)) {
			$scenarios_array = json_decode($scenario, true) ?: [];
		} else {
			$scenarios_array = [];
		}

		$scenario_array = array_merge(
			$scenarios_array[$scenario_id] ?? [],
			[
				'id' => $scenario_id,
				'status' => $status,
				'updated_at' => gmdate(DATE_ATOM),
			],
			$addtional ?: []
		);

		if (self::STATUS_ACTIVE === $status && !isset($scenario_array['started_at'])) {
			$scenario_array = array_merge($scenario_array, ['started_at' => gmdate(DATE_ATOM)]);
		}
		if (self::STATUS_COMPLETED === $status && isset($scenario_array['next'])) {
			unset($scenario_array['next']);
		}
		if (self::STATUS_COMPLETED === $status && isset($scenario_array['next_date'])) {
			unset($scenario_array['next_date']);
		}

		if (!empty($unsets)) {
			foreach ($unsets as $unset) {
				unset($scenario_array[$unset]);
			}
		}

		$scenarios_array[$scenario_id] = $scenario_array;

		// error_log("Scenario ID: $scenario_id, Status: $status, User ID: $line_user_id");

		$updated_scenarios = json_encode($scenarios_array);

		return $wpdb->update(
			$table_name,
			['scenarios' => $updated_scenarios],
			['line_id' => $line_user_id, 'channel_prefix' => $channel_prefix]
		) !== false;
	}

	/**
	 * Return scenario array object post_id and title
	 */
	static function get_scenario_name_array() {
		$args          = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$posts         = get_posts($args);
		$scenario_array = array();
		foreach ($posts as $post) {
			$scenario_array[$post->ID] = $post->post_title;
		}
		return $scenario_array;
	}

	/**
	 * シナリオデータをカスタム投稿タイプから取得
	 * @param int $scenario_id シナリオID
	 * @return array シナリオデータ
	 */
	public static function getScenario(int $scenario_id): array {
		// only status is publish
		$scenario = get_post($scenario_id);
		if (is_null($scenario) || 'publish' !== $scenario->post_status) {
			return [];
		}
		// print_r($scenario);

		$scenario_data = get_post_meta($scenario_id, self::META_KEY_DATA, true);
		if (empty($scenario_data)) {
			return [];
		}
		// print_r($scenario_data);

		return $scenario_data[0];
	}

	/**
	 * 指定されたステップを実行する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param string|null $step_id ステップID(nullの場合は最初のステップから)
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	public static function execute_step(int $scenario_id, ?string $step_id, string $line_user_id, string $secret_prefix, ?string $last_executed_at = null): array {
		$scenario = self::getScenario($scenario_id);
		if (empty($scenario)) {
			return array(
				'result' => 'error',
				'message' => __('Scenario not found', lineconnect::PLUGIN_NAME),
			);
		}

		$line_user_scenario_status = self::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
		if (isset($line_user_scenario_status['status']) && $line_user_scenario_status['status'] === self::STATUS_COMPLETED) {
			return array(
				'result' => 'error',
				'message' => __('Scenario already completed', lineconnect::PLUGIN_NAME),
			);
		}

		$step_data = null;
		foreach ($scenario as $step) {
			if ($step_id === null || $step['id'] === $step_id) {
				$step_data = $step;
				break;
			}
		}
		if (empty($step_data)) {
			$log = array(
				'step' => $step_id,
				'date' => gmdate(DATE_ATOM),
				'result' => 'error',
				'message' => __('Step not found', lineconnect::PLUGIN_NAME),
			);
			$logs = array_merge($line_user_scenario_status['logs'] ?? [], [$log]);
			self::update_scenario_status($scenario_id, self::STATUS_ERROR, $line_user_id, $secret_prefix, ['logs' => $logs]);
			return $log;
		}
		$event = new stdClass();
		$event->source = new stdClass();
		$event->source->userId = $line_user_id;
		$condition_matched = Condition::evaluate_conditions($step_data['condition'], $secret_prefix, $line_user_id);
		if ($condition_matched) {
			$action_result = Action::do_action($step_data['actions'], $step_data['chains'] ?? null, $event, $secret_prefix, $scenario_id);
			if (! empty($action_result['messages'])) {
				$channel = lineconnect::get_channel($secret_prefix);
				$multimessage = Builder::createMultiMessage($action_result['messages']);
				$response = Sender::sendPushMessage($channel, $line_user_id, $multimessage);
			}
			if (!$action_result['success'] || (isset($response['success']) && !$response['success'])) {
				$log = array(
					'step' => $step_data['id'],
					'date' => gmdate(DATE_ATOM),
					'result' => 'error',
					'message' => !$action_result['success'] ? $action_result['results'] : ($response['message'] ?? ''),
				);
				$logs = array_merge($line_user_scenario_status['logs'] ?? [], [$log]);
				self::update_scenario_status($scenario_id, self::STATUS_ERROR, $line_user_id, $secret_prefix, ['logs' => $logs]);
				return $log; // Returning error log if there's an error
			}
		}
		$log = array(
			'step' => $step_data['id'],
			'date' => gmdate(DATE_ATOM),
			'result' => $condition_matched ? 'success' : 'skip',
			'message' => '',
		);
		$logs = array_merge($line_user_scenario_status['logs'] ?? [], [$log]);
		$addtional = ['logs' => $logs];

		// 条件に一致して、アクションにステップジャンプが含まれている場合は、すでに次のステップを指定済みとする
		$next = $next_date = null;
		if ($condition_matched) {
			$new_status = self::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
			if (isset($new_status['next']) && $new_status['next'] !== $line_user_scenario_status['next']) {
				// nextが更新されている場合はそのnextを採用する
				$status = $new_status['status'];
				if ($status === self::STATUS_ACTIVE) {
					$next = $new_status['next'];
					$next_date = $new_status['next_date'] ?? null;
					$addtional['next'] = $next;
					$addtional['next_date'] = $next_date;
				}
			}
		}

		if ($next === null) {
			$next = $step_data['next'] ?? null;
			if (\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($next)) {
				// get step after this step
				$next = null;
				$found = false;
				foreach ($scenario as $step) {
					if ($found) {
						$next = $step['id'];
						break;
					}
					if ($step['id'] === $step_data['id']) {
						$found = true;
					}
				}
			}


			// set next step
			if ((isset($step_data['stop']) && $step_data['stop']) || \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($next)) {
				$status = self::STATUS_COMPLETED;
				// self::update_scenario_status($scenario_id, self::STATUS_COMPLETED, $line_user_id, $secret_prefix,  ['logs' => $logs]);
			} else {
				$status = self::STATUS_ACTIVE;
				$last_executed_at = $last_executed_at ?: $line_user_scenario_status['next_date'] ?? gmdate(DATE_ATOM);
				$next_date = Schedule::getNextSchedule($step_data['schedule'] ?? [], $last_executed_at);
				$now = new \DateTime();
				if (!$next_date || $now > new \DateTime($next_date)) { // if next date is in the past set current date
					// error_log(print_r($next_date, true));
					// error_log(print_r($now, true));
					$next_date = gmdate(DATE_ATOM);
				}
				$addtional['next'] = $next;
				$addtional['next_date'] = $next_date;
			}
		}
		self::update_scenario_status($scenario_id, $status, $line_user_id, $secret_prefix, $addtional);


		return $log;
	}

	/*
	 * 特定のステップへ移動
	 * @param ?string $step_id ステップID
	 * @param ?string $next_date 次回実行日時
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	public static function set_scenario_step(int $scenario_id, ?string $step_id = null, ?string $next_date = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$scenario = self::getScenario($scenario_id);
		if (empty($scenario)) {
			return array(
				'result' => 'error',
				'message' => __('Scenario not found', lineconnect::PLUGIN_NAME),
			);
		}

		$line_user_scenario_status = self::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);

		$current_step_data = null;
		foreach ($scenario as $step) {
			if (isset($line_user_scenario_status['next']) && ($line_user_scenario_status['next'] === null || $step['id'] === $line_user_scenario_status['next'])) {
				$current_step_data = $step;
				break;
			}
		}

		$next_step_data = null;
		foreach ($scenario as $step) {
			if ($step_id === null || $step['id'] === $step_id) {
				$next_step_data = $step;
				break;
			}
		}

		if (empty($next_step_data)) {
			return [
				'result' => 'error',
				'message' => __('Step not found', lineconnect::PLUGIN_NAME)
			];
		}

		$next = $next_step_data['id'] ?? null;
		$next_date = $next_date ? gmdate(DATE_ATOM, strtotime($next_date)) : ($line_user_scenario_status['next_date'] ?? (Schedule::getNextSchedule($current_step_data['schedule'] ?? [], gmdate(DATE_ATOM)) ?? gmdate(DATE_ATOM)));
		$now = new \DateTime();
		if (!$next_date || $now > new \DateTime($next_date)) { // if next date is in the past set current date
			$next_date = gmdate(DATE_ATOM);
		}


		self::update_scenario_status($scenario_id, self::STATUS_ACTIVE, $line_user_id, $secret_prefix, ['next' => $next, 'next_date' => $next_date]);

		return [
			'result' => 'success',
			'next' => $next,
			'next_date' => $next_date
		];
	}
}
