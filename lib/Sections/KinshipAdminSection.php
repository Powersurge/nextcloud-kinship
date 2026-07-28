<?php

declare(strict_types=1);

namespace OCA\Kinship\Sections;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class KinshipAdminSection implements IIconSection {
	private IL10N $l;
	private IURLGenerator $urlGenerator;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function getID(): string {
		return 'kinship';
	}

	public function getName(): string {
		return $this->l->t('Kinship');
	}

	public function getPriority(): int {
		return 80;
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('kinship', 'app-dark.svg');
	}
}
