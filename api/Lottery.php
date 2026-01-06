<?php
// Lightweight Lottery helper to perform weighted draws on backend.
class Lottery {
	// Draw a prize from an array of items with weights.
	// prizes: array of ['name'=>string, 'weight'=>float|int, 'type'=>..., 'code'=>optional, ...]
	public static function draw(array $prizes): ?array {
		if (empty($prizes)) {
			return null;
		}

		// Determine scaling factor to support float weights without losing precision
		$maxDecimals = 0;
		foreach ($prizes as $p) {
			$w = isset($p['weight']) ? $p['weight'] : 0;
			// treat non-numeric as 0
			if (!is_numeric($w)) $w = 0;
			$parts = explode('.', (string)$w);
			if (isset($parts[1])) {
				$maxDecimals = max($maxDecimals, strlen(rtrim($parts[1], '0')));
			}
		}
		// cap decimals to avoid extremely large integers
		$maxDecimals = min($maxDecimals, 6);
		$multiplier = (int) pow(10, $maxDecimals ?: 0);

		$total = 0;
		$intWeights = [];
		foreach ($prizes as $p) {
			$w = isset($p['weight']) ? (float)$p['weight'] : 0.0;
			$intW = max(0, (int) round($w * $multiplier));
			$intWeights[] = $intW;
			$total += $intW;
		}

		if ($total <= 0) {
			// fallback: return first item
			return $prizes[0] ?? null;
		}

		$rand = random_int(1, $total);
		$acc = 0;
		foreach ($prizes as $idx => $p) {
			$acc += $intWeights[$idx];
			if ($rand <= $acc) {
				return $p;
			}
		}

		// fallback
		return $prizes[0] ?? null;
	}
}
?> 

