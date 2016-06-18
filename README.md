# Magento2 Scope Hint

Displays a hint when a configuration value is overwritten on a lower scope (website or store view).

Based on the Magento 1 module. [AvS_ScopeHint](https://github.com/AOEpeople/Aoe_TemplateHints)

## Working Features:
Showing the Path of the Config Element in System

## Not Working Features
Product Page has a big Bug(HTML-Markup was changed due in M2). Need to be refactored.
Javascript will not work and need to be refactored to jQuery
Layout XML need to be refactored
a new Icon need to be integrated into the Plugin. Havent found a new one yet



## Other Improvements
Change everything from Core-Rewrites to Plugins

## Quick Installation

        composer config repositories.firegento_scopehint vcs git@github.com:EliasKotlyar/ScopeHint.git
        composer require firegento/scopehint:dev-master
        bin/magento module:enable Firegento_ScopeHint
        bin/magento setup:upgrade
