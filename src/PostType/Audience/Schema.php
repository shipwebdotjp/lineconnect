<?php
namespace Shipweb\LineConnect\PostType\Audience;

use Shipweb\LineConnect\Core\LineConnect;

class Schema {
    static function get_schema() {
        return array(
			'type'       => 'object',
			'title'      => __('Audience', lineconnect::PLUGIN_NAME),
			'properties' => array(
				'condition' => array(
					'$ref' => '#/definitions/condition',
				),
			),
			'required'   => array(
				'condition',
			),
			'definitions' => array(
				'condition' => array(
					'title' => __('Audience condition', lineconnect::PLUGIN_NAME),
					'type' => 'object',
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Audience condition group', lineconnect::PLUGIN_NAME),
							'minItems' => 1,
							'items' => array(
								'type'  => 'object',
								'title' => __('Audience condition', lineconnect::PLUGIN_NAME),
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
												'const' => 'link',
												'title' => __('Link status', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'role',
												'title' => __('Role', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'lineUserId',
												'title' => __('Line user ID', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'wpUserId',
												'title' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'user_email',
												'title' => __('Email', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'user_login',
												'title' => __('User login name', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'display_name',
												'title' => __('User display name', lineconnect::PLUGIN_NAME),
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
												'title' => __('Audience condition group', lineconnect::PLUGIN_NAME),
											),
										),
									)
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
														'const' => 'link',
													),
													'link' => array(
														'type' => 'object',
														'title' => __('Link status', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'broadcast',
																		'title' => __('All friends(Broadcast)', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'all',
																		'title' => __('All recognized friends(Multicast)', lineconnect::PLUGIN_NAME),
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
													'match' => array(
														'type' => 'string',
														'title' => __('Match type', lineconnect::PLUGIN_NAME),
														'description' => __('Select how to match user roles', lineconnect::PLUGIN_NAME),
														'default' => 'role__in',
														'anyOf' => array(
															array(
																'const' => 'role',
																'title' => __('Must have all roles (AND)', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'role__in',
																'title' => __('Must have at least one role (OR)', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'role__not_in',
																'title' => __('Must not have these roles (NOT)', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'lineUserId',
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
														'const' => 'wpUserId',
													),
													'wpUserId' => array(
														'type' => 'array',
														'title' => __('WordPress User ID', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'integer',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'user_email',
													),
													'user_email' => array(
														'type' => 'array',
														'title' => __('Email', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'string',
															'format' => 'email',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'user_login',
													),
													'user_login' => array(
														'type' => 'array',
														'title' => __('User login name', lineconnect::PLUGIN_NAME),
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
														'const' => 'display_name',
													),
													'display_name' => array(
														'type' => 'array',
														'title' => __('User display name', lineconnect::PLUGIN_NAME),
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
							'default' => 'and',
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
			),
		);
    }

    static function get_uischema() {
        return array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),

			'condition' => array(
				// 'ui:classNames' => 'title-hidden',
				'ui:options' => array(
					"label" => false,
				),
				'conditions' => array(
					'ui:options' => array(
						'addText' =>  __('Add condition', lineconnect::PLUGIN_NAME),
						'copyable' => true,
					),
					'items' => array(
						'userId' => array(
							'ui:options' => array(
								'addText' =>  __('Add LINE user ID', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'wpUserId' => array(
							'ui:options' => array(
								'addText' =>  __('Add WordPress user ID', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'email' => array(
							'ui:options' => array(
								'addText' =>  __('Add email', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'username' => array(
							'ui:options' => array(
								'addText' =>  __('Add username', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'userMeta' => array(
							'ui:options' => array(
								'addText' =>  __('Add user meta', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'profile' => array(
							'ui:options' => array(
								'addText' =>  __('Add profile data', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'condition' => array(
							'$.ref' => 'condition',
						),

					),
				),
			),
		);
    }
}