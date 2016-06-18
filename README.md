# Magento2 Scope Hint

Displays a hint when a configuration value is overwritten on a lower scope (website or store view).

Based on the Magento 1 module. [AvS_ScopeHint](https://github.com/AOEpeople/Aoe_TemplateHints)


## Quick Installation

        composer config repositories.firegento_scopehint vcs git@github.com:EliasKotlyar/ScopeHint.git
        composer require firegento/scopehint:dev-master
        bin/magento module:enable Firegento_ScopeHint
        bin/magento setup:upgrade
