<?php namespace Hampel\HomePageTitle;

use XF\AddOn\AbstractSetup;

class Setup extends AbstractSetup
{
    // XF1 1.x stored the title in this option; 2.x uses hampelHomePageTitle
    const LEGACY_OPTION = 'homepageTitle';

    // holds the XF1 value across the data import, which runs between upgrade() and postUpgrade()
    const LEGACY_REGISTRY_KEY = 'hampelHomePageTitleLegacy';

    public function install(array $stepParams = [])
    {
        // nothing to do
    }

    public function upgrade(array $stepParams = [])
    {
        // Upgrading from the XF1 add-on: preUpgrade() has already moved the old option to this
        // add-on's id, so the data import that follows deletes it. Read it now, set it later.
        $value = $this->db()->fetchOne("
            SELECT option_value
            FROM xf_option
            WHERE option_id = ?
        ", self::LEGACY_OPTION);

        if (is_string($value) && trim($value) !== '')
        {
            \XF::registry()->set(self::LEGACY_REGISTRY_KEY, $value);
        }
    }

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->migrateLegacyTitle();

        if (\XF::$versionId >= 2030000) { // XF 2.3+
            $this->enqueuePostUpgradeCleanUp();
        }
    }

    public function uninstall(array $stepParams = [])
    {
        \XF::registry()->delete(self::LEGACY_REGISTRY_KEY);
    }

    protected function migrateLegacyTitle()
    {
        $registry = \XF::registry();
        $value = $registry->get(self::LEGACY_REGISTRY_KEY);
        if (!is_string($value) || $value === '')
        {
            return;
        }

        // never overwrite a title that has already been set on the 2.x add-on; read the database
        // rather than \XF::options(), which may predate the import in this request
        $current = $this->db()->fetchOne("
            SELECT option_value
            FROM xf_option
            WHERE option_id = 'hampelHomePageTitle'
        ");

        if ($current !== false && trim($current) === '')
        {
            $this->app->repository('XF:Option')->updateOption('hampelHomePageTitle', $value);
        }

        $registry->delete(self::LEGACY_REGISTRY_KEY);
    }
}
