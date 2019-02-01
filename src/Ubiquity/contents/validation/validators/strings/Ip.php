<?php

namespace Ubiquity\contents\validation\validators\strings;

class Ip {
		const V4 = '4';
		const V6 = '6';
		const ALL = 'all';
		// adds FILTER_FLAG_NO_PRIV_RANGE flag (skip private ranges)
		const V4_NO_PRIV = '4_no_priv';
		const V6_NO_PRIV = '6_no_priv';
		const ALL_NO_PRIV = 'all_no_priv';
		// adds FILTER_FLAG_NO_RES_RANGE flag (skip reserved ranges)
		const V4_NO_RES = '4_no_res';
		const V6_NO_RES = '6_no_res';
		const ALL_NO_RES = 'all_no_res';
		// adds FILTER_FLAG_NO_PRIV_RANGE and FILTER_FLAG_NO_RES_RANGE flags (skip both)
		const V4_ONLY_PUBLIC = '4_public';
		const V6_ONLY_PUBLIC = '6_public';
		const ALL_ONLY_PUBLIC = 'all_public';
}

