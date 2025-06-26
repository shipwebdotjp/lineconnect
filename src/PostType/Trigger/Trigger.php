<?php

namespace Shipweb\LineConnect\PostType\Trigger;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Action\Action;

class Trigger {
    const NAME = 'trigger';
    const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;
    const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;
    const META_KEY_DATA = self::NAME . '-data';
    const PARAMETER_DATA = LineConnect::PLUGIN_PREFIX . self::META_KEY_DATA;
    const SCHEMA_VERSION = 1;
    const POST_TYPE = LineConnect::PLUGIN_PREFIX . self::NAME;

    static function get_schema() {
        $schema = array(
			'type'       => 'object',
			'properties' => array(
				'triggers' => array(
					'type'       => 'array',
					'title'      => __('Triggers', lineconnect::PLUGIN_NAME),
					'items' => array(
						//'type'       => 'object',
						//'title'      => __('Trigger', lineconnect::PLUGIN_NAME),
						//'properties' => array(),
					),
				),
				'action'  => array(
					'title' => __('Action', lineconnect::PLUGIN_NAME),
					'type'  => 'array',
					'items' => array(
						'type'     => 'object',
						'oneOf'    => array(),
						'required' => array(
							'parameters',
						),
					),
				),
				'chain' => array(
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
			),
			'definitions' => array(
				'condition' => array(
					'type' => 'object',
					'title' => __('Source condition', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Source condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Source condition', lineconnect::PLUGIN_NAME),
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
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Source condition group', lineconnect::PLUGIN_NAME),
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
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
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
																			'role' => array(
																				'$ref' => '#/definitions/role',
																			),
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
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
																							'$ref' => '#/definitions/compare_dependencies',
																						),
																					),
																				),
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
																							'$ref' => '#/definitions/compare_dependencies',
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
																			'groupId' => array(
																				'type' => 'array',
																				'title' => __('LINE group ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
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
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
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
				'keyword' => array(
					'type' => 'object',
					'title' => __('Keyword', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Keyword condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Keyword condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' => __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Keyword condition group', lineconnect::PLUGIN_NAME),
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
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'keyword',
																		'title' => __('Keyword', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'query',
																		'title' => __('Query string', lineconnect::PLUGIN_NAME),
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
																				'const' => 'keyword',
																			),
																			'keyword' => array(
																				'type' => 'object',
																				'title' =>  __('Keyword', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'keyword' => array(
																						'type' => 'string',
																						'title' => __('Keyword', lineconnect::PLUGIN_NAME),
																						'description' => __('Keyword to match', lineconnect::PLUGIN_NAME),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'startsWith',
																								'title' => __('Starts with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'endsWith',
																								'title' => __('Ends with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'regexp',
																								'title' => __('Regular expression', lineconnect::PLUGIN_NAME),
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
																				'const' => 'query',
																			),
																			'query' => array(
																				'type' => 'object',
																				'title' => __('Query string', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'parameters' => array(
																						'type' => 'array',
																						'title' => __('Parameters', lineconnect::PLUGIN_NAME),
																						'items' => array(
																							'type' => 'object',
																							'title' => __('Parameter', lineconnect::PLUGIN_NAME),
																							'properties' => array(
																								'key' => array(
																									'type' => 'string',
																									'title' => __('Key', lineconnect::PLUGIN_NAME),
																								),
																								'value' => array(
																									'type' => 'string',
																									'title' => __('Value', lineconnect::PLUGIN_NAME),
																								),
																							),
																						),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
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
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/keyword',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type' => 'string',
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
				'compare_dependencies' => array(
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
				'postbackparams' => array(
					'type' => 'object',
					'title' => __('Parameters', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Parameters condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Parameter condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' => __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Parameter condition group', lineconnect::PLUGIN_NAME),
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
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'newRichMenuAliasId',
																		'title' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'status',
																		'title' => __('Status', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'date',
																		'title' => __('Date', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'time',
																		'title' => __('Time', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'datetime',
																		'title' => __('Date and time', lineconnect::PLUGIN_NAME),
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
																				'const' => 'newRichMenuAliasId',
																			),
																			'newRichMenuAliasId' => array(
																				'type' => 'object',
																				'title' =>  __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'newRichMenuAliasId' => array(
																						'type' => 'string',
																						'title' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																						'description' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'startsWith',
																								'title' => __('Starts with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'endsWith',
																								'title' => __('Ends with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'regexp',
																								'title' => __('Regular expression', lineconnect::PLUGIN_NAME),
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
																				'const' => 'status',
																			),
																			'status' => array(
																				'type' => 'array',
																				'title' => __('Status', lineconnect::PLUGIN_NAME),
																				'uniqueItems' => true,
																				'items' => array(
																					'type' => 'string',
																					'oneOf' => array(
																						array(
																							'const' => 'SUCCESS',
																							'title' => __('Success', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'RICHMENU_ALIAS_ID_NOTFOUND',
																							'title' => __('Richmenu alias ID not found', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'RICHMENU_NOTFOUND',
																							'title' => __('Richmenu not found', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'FAILED',
																							'title' => __('Failed', lineconnect::PLUGIN_NAME),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'date',
																			),
																			'date' => array(
																				'type' => 'object',
																				'title' =>  __('Date', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'date' => array(
																						'type' => 'string',
																						'title' => __('Date', lineconnect::PLUGIN_NAME),
																						'description' => __('Date: YYYY-MM-DD', lineconnect::PLUGIN_NAME),
																						"format" => "date",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
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
																				'const' => 'time',
																			),
																			'time' => array(
																				'type' => 'object',
																				'title' =>  __('Time', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'time' => array(
																						'type' => 'string',
																						'title' => __('Time', lineconnect::PLUGIN_NAME),
																						'description' => __('Time: hh:mm', lineconnect::PLUGIN_NAME),
																						"format" => "time",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
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
																				'const' => 'datetime',
																			),
																			'datetime' => array(
																				'type' => 'object',
																				'title' =>  __('DateTime', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'datetime' => array(
																						'type' => 'string',
																						'title' => __('DateTime', lineconnect::PLUGIN_NAME),
																						'description' => __('DateTime: YYYY-MM-DDThh:mm', lineconnect::PLUGIN_NAME),
																						"format" => "date-time",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
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
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/postbackparams',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type' => 'string',
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
			),
		);
        Action::build_action_schema_items($schema['properties']['action']['items']['oneOf']);

		$all_roles = array();
		foreach (wp_roles()->roles as $role_name => $role) {
			$all_roles[] = array(
				'const' => esc_attr($role_name),
				'title' => translate_user_role($role['name']),
			);
		}
		$schema['definitions']['role']['items']['oneOf'] = $all_roles;

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
		$schema['definitions']['secret_prefix']['items']['oneOf'] = $all_channels;


		$trigger_schema_bytype = array();
		foreach (self::get_types() as $type => $type_schema) {
			$trigger_schema_bytype[$type] = $schema;
			$trigger_schema_bytype[$type]['properties']['triggers']['items'] = $type_schema;
		}

		return $trigger_schema_bytype;
    }

    static function get_types() {
        return array(
			'webhook' => array(
				'type' => 'object',
				'title' => __('Webhook', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'type' => array(
						'title' => __('Event type', lineconnect::PLUGIN_NAME),
						'type' => 'string',
						'oneOf' => array(
							array(
								'const' => 'message',
								'title' => __('Message', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'postback',
								'title' => __('Post back', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'accountLink',
								'title' => __('Account Link', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'follow',
								'title' => __('Follow', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'unfollow',
								'title' => __('Unfollow', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'videoPlayComplete',
								'title' => __('Video play complete', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'join',
								'title' => __('Join', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'leave',
								'title' => __('Leave', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'memberJoined',
								'title' => __('Member joined', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'memberLeft',
								'title' => __('Member left', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'unsend',
								'title' => __('Unsend', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'membership',
								'title' => __('Membership', lineconnect::PLUGIN_NAME),
							),
						),
					),
					'condition' => array(
						'$ref' => '#/definitions/condition',
					),
				),
				'required'   => array(
					'type',
				),
				'dependencies' => array(
					'type' => array(
						'oneOf' => array(
							array(
								'properties' => array(
									'type' => array(
										'const' => 'message',
									),
									'message' => array(
										'title' => __('Message', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'type' => array(
												'type' => 'string',
												'title' => __('Message type', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'text',
														'title' => __('Text', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'image',
														'title' => __('Image', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'video',
														'title' => __('Video', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'audio',
														'title' => __('Audio', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'file',
														'title' => __('File', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'location',
														'title' => __('Location', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'sticker',
														'title' => __('Sticker', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'required' => array(
											'type',
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'text',
															),
															'text' => array(
																'$ref' => '#/definitions/keyword',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'image',
															)
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'video',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'audio',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'file',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'location',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'sticker',
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
										'const' => 'postback',
									),
									'postback' => array(
										'title' => __('Post back', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'data' => array(
												'$ref' => '#/definitions/keyword',
											),
											'params' => array(
												'$ref' => '#/definitions/postbackparams',
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'accountLink',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'follow',
									),
									'follow' => array(
										'title' => __('Follow', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'isUnblocked' => array(
												'type' => 'string',
												'title' => __('Unblocked', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'any',
														'title' => __('Any', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'add',
														'title' => __('Add Freind', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'unblocked',
														'title' => __('Unblocked', lineconnect::PLUGIN_NAME),
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
										'const' => 'unfollow',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'videoPlayComplete',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'join',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'leave',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'memberJoined',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'memberLeft',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'unsend',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'membership',
									),
									'membership' => array(
										'title' => __('Membership', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'type' => array(
												'type' => 'string',
												'title' => __('Type', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'joined',
														'title' => __('Joined', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'left',
														'title' => __('Left', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'renewed',
														'title' => __('Renewed', lineconnect::PLUGIN_NAME),
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
			'schedule' => array(
				// 'type' => 'object',
				// 'title' => __('Schedules', lineconnect::PLUGIN_NAME),
				// 'items' => array(
				'type' => 'object',
				'title' => __('Schedule', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'type' => array(
						'title' => __('Schedule type', lineconnect::PLUGIN_NAME),
						'type' => 'string',
						'oneOf' => array(
							array(
								'const' => 'once',
								'title' => __('Once', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'repeat',
								'title' => __('Repeat', lineconnect::PLUGIN_NAME),
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
										'const' => 'once',
									),
									'once' => array(
										'type' => 'object',
										'title' => __('Once', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'datetime' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('Date and time', lineconnect::PLUGIN_NAME),
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'repeat',
									),
									'repeat' => array(
										'type' => 'object',
										'title' => __('Repeat', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'every' => array(
												'type' => 'string',
												'title' => __('Every', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'hour',
														'title' => __('Hour', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'day',
														'title' => __('Day', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'date',
														'title' => __('Date', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'week',
														'title' => __('Week', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'month',
														'title' => __('Month', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'year',
														'title' => __('Year', lineconnect::PLUGIN_NAME),
													),
												),
											),
											'start' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('Start date', lineconnect::PLUGIN_NAME),
											),
											'end' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('End date', lineconnect::PLUGIN_NAME),
											),
											'lag' => array(
												'type' => 'integer',
												'title' => __('Beforehand notice (min)', lineconnect::PLUGIN_NAME),
												'description' => __('How many minutes in advance notice', lineconnect::PLUGIN_NAME),
											),
										),
										'required' => array(
											'every',
											'start',
										),
										'dependencies' => array(
											'every' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'every' => array(
																'const' => 'hour',
															),
															'hour' => array(
																'type' => 'array',
																'title' => __('Hour', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
																	'type' => 'integer',
																	'oneOf' => array(
																		array(
																			'const' => 0,
																			'title' => __('0 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 1,
																			'title' => __('1 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 2,
																			'title' => __('2 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 3,
																			'title' => __('3 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 4,
																			'title' => __('4 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 5,
																			'title' => __('5 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 6,
																			'title' => __('6 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 7,
																			'title' => __('7 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 8,
																			'title' => __('8 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 9,
																			'title' => __('9 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 10,
																			'title' => __('10 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 11,
																			'title' => __('11 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 12,
																			'title' => __('12 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 13,
																			'title' => __('1 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 14,
																			'title' => __('2 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 15,
																			'title' => __('3 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 16,
																			'title' => __('4 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 17,
																			'title' => __('5 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 18,
																			'title' => __('6 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 19,
																			'title' => __('7 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 20,
																			'title' => __('8 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 21,
																			'title' => __('9 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 22,
																			'title' => __('10 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 23,
																			'title' => __('11 pm', lineconnect::PLUGIN_NAME),
																		),
																	),
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'day',
															),
															'day' => array(
																'type' => 'array',
																'title' => __('Day', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'object',
																	'properties' => array(
																		'type' => array(
																			'type' => 'string',
																			'title' => __('Way of Calc', lineconnect::PLUGIN_NAME),
																			'oneOf' => array(
																				array(
																					'const' => 'nthday',
																					'title' => __('nth day', lineconnect::PLUGIN_NAME),
																				),
																				array(
																					'const' => 'nthweek',
																					'title' => __('nth Week in a month', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		'number' => array(
																			'type' => 'array',
																			'title' => __('Day of the week in the month', lineconnect::PLUGIN_NAME),
																			'uniqueItems' => true,
																			'items' => array(
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
																				),
																			),
																		),
																		'day' => array(
																			'type' => 'array',
																			'title' => __('Day', lineconnect::PLUGIN_NAME),
																			'uniqueItems' => true,
																			'items' => array(
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
																			),
																		),
																	),
																	'dependencies' => array(
																		'type' => array(
																			'oneOf' => array(
																				array(
																					'properties' => array(
																						'type' => array(
																							'const' => 'nthweek',
																						),
																						'startdayofweek' => array(
																							'type' => 'integer',
																							'title' => __('First day of the week', lineconnect::PLUGIN_NAME),
																							'oneOf' => array(
																								array(
																									'const' => 0,
																									'title' => __('Sunday', lineconnect::PLUGIN_NAME),
																								),
																								array(
																									'const' => 1,
																									'title' => __('Monday', lineconnect::PLUGIN_NAME),
																								),
																							),
																						),
																					),
																				),
																				array(
																					'properties' => array(
																						'type' => array(
																							'const' => 'nthday',
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
															'every' => array(
																'const' => 'date',
															),
															'date' => array(
																'type' => 'array',
																'title' => __('Date', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
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
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'week',
															),
															'week' => array(
																'type' => 'array',
																'title' => __('Week number', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'integer',
																	'minimum' => 1,
																	'maximum' => 52,
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'month',
															),
															'month' => array(
																'type' => 'array',
																'title' => __('Month', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
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
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'year',
															),
															'year' => array(
																'type' => 'array',
																'title' => __('Year', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'integer',
																	'minimum' => 2024,
																	'maximum' => 2099,
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
				//),
			),
		);
    }

    static function get_uischema(){
        return array(
            'ui:submitButtonOptions' => array(
                'norender' => true,
            ),
            'triggers' => array(
                'items' => array(
                    'ui:order' => array(
                        'type',
                        'message',
                        'postback',
                        'follow',
                        '*',
                    ),
                    'condition' => array(
                        'conditions' => array(
                            'items' => array(
                                'ui:order' => array(
                                    'type',
                                    'source',
                                    'secret_prefix',
                                    '*',
                                ),
                                /*
                                'not' => array(
                                    'ui:widget' => 'select',
                                ),*/
                            ),
                            'ui:options' => array(
                                'addText' =>  __('Add source condition', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                    ),
                    'message' => array(
                        'text' => array(
                            'conditions' => array(
                                'items' => array(
                                    'ui:order' => array(
                                        'type',
                                        'source',
                                        '*',
                                    ),
                                    /*
                                    'not' => array(
                                        'ui:widget' => 'select',
                                    ),*/
                                    'source' => array(
                                        'query' => array(
                                            'parameters' => array(
                                                'ui:options' => array(
                                                    'addText' =>  __('Add query parameter', lineconnect::PLUGIN_NAME),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                                'ui:options' => array(
                                    'addText' =>  __('Add message condition', lineconnect::PLUGIN_NAME),
                                ),
                            ),
                        ),
                    ),
                    'postback' => array(
                        'data' => array(
                            'conditions' => array(
                                'items' => array(
                                    'ui:order' => array(
                                        'type',
                                        'source',
                                        '*',
                                    ),
                                    /*
                                    'not' => array(
                                        'ui:widget' => 'select',
                                    ),*/
                                ),
                                'ui:options' => array(
                                    'addText' =>  __('Add postback condition', lineconnect::PLUGIN_NAME),
                                ),
                            ),
                        ),
                    ),
                    'repeat' => array(
                        'ui:order' => array(
                            'every',
                            'hour',
                            'day',
                            'date',
                            'week',
                            'month',
                            'year',
                            '*',
                        ),
                        'hour' => array(
                            'ui:widget' => 'checkboxes',
                            'ui:options' => array(
                                'inline' => true,
                            ),
                        ),
                        'day' => array(
                            'items' => array(
                                'number' => array(
                                    'ui:widget' => 'checkboxes',
                                    'ui:options' => array(
                                        'inline' => true,
                                    ),
                                ),
                                'day' => array(
                                    'ui:widget' => 'checkboxes',
                                    'ui:options' => array(
                                        'inline' => true,
                                    ),
                                ),
                            ),
                            'ui:options' => array(
                                'addText' =>  __('Add day', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                        'date' => array(
                            'ui:widget' => 'checkboxes',
                            'ui:options' => array(
                                'inline' => true,
                            ),
                        ),
                        'week' => array(
                            'ui:options' => array(
                                'addText' =>  __('Add week', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                        'month' => array(
                            'ui:widget' => 'checkboxes',
                            'ui:options' => array(
                                'inline' => true,
                            ),
                        ),
                        'year' => array(
                            'ui:options' => array(
                                'addText' =>  __('Add year', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                    ),
                ),
                'ui:options' => array(
                    'addText' =>  __('Add trigger', lineconnect::PLUGIN_NAME),
                ),
            ),
            'action'                 => array(
                'items' => array(
                    'action_name' => array(
                        'ui:style' => array(
                            'display' => 'none',
                        ),
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
                    'addText' =>  __('Add action', lineconnect::PLUGIN_NAME),
                ),
            ),
            'chain' => array(
                'ui:options' => array(
                    'addText' =>  __('Add chain', lineconnect::PLUGIN_NAME),
                ),
            ),
        );
    }

    static function get_type_schema(){
        return array(
			'type'       => 'object',
			'properties' => array(
				'type' => array(
					'type'  => 'string',
					'title' => __('Trigger type', lineconnect::PLUGIN_NAME),
					'oneOf' => array(
						array(
							'const' => 'webhook',
							'title' => __('Webhook', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'schedule',
							'title' => __('Schedule', lineconnect::PLUGIN_NAME),
						),
					),
				),
			),
		);
    }

    static function get_type_uischema(){
        return array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'type' => array(
				'ui:description' => __('Choose trigger type.', lineconnect::PLUGIN_NAME),
				'ui:widget' => 'radio',
				'ui:options' => array(
					'inline' => true,
				),
			),
		);
    }

}