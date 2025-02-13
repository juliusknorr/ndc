<?php

class RoutesFlavour {

	public function hasFlavour(): bool {
		// check if routes.php exists
		return true;
	}

	public function apply(): void {
		if ($this->hasFlavour()) {
			return;
		}
		$routesContent = "<?php\n\nreturn [];";
		// write to routes
	}

}
