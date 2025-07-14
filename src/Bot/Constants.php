<?php

namespace Shipweb\LineConnect\Bot;

class Constants {

	/**
	 * イベントタイプ
	 */
	const WH_EVENT_TYPE = array(
		1  => 'message',
		2  => 'unsend',
		3  => 'follow',
		4  => 'unfollow',
		5  => 'join',
		6  => 'leave',
		7  => 'memberJoined',
		8  => 'memberLeft',
		9  => 'postback',
		10 => 'videoPlayComplete',
		11 => 'beacon',
		12 => 'accountLink',
		13 => 'things',
		14 => 'membership',
		91 => 'broadcast',
		92 => 'push',
		93 => 'multicast',
		94 => 'reply',
		95 => 'narrowcast',
	);

	/**
	 * ソースタイプ
	 */
	const WH_SOURCE_TYPE = array(
		1  => 'user',
		2  => 'group',
		3  => 'room',
		11 => 'bot',
		12 => 'system',
		13 => 'admin',
		14 => 'user',
	);

	/**
	 * メッセージタイプ
	 */
	const WH_MESSAGE_TYPE = array(
		1 => 'text',
		2 => 'image',
		3 => 'video',
		4 => 'audio',
		5 => 'file',
		6 => 'location',
		7 => 'sticker',
	);

	/**
	 * ステータス
	 */
	const WH_STATUS = array(
		0  => 'pending',
		1  => 'sent',
		9  => 'failed',
	);
}
