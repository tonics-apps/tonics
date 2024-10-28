<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Boot;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Handler\Router;

/**
 * Class HttpMessageProvider
 * @package App
 */
class HttpMessageProvider implements ServiceProvider
{

    private Router $router;

    /**
     * @param Router $router
     */
    public function __construct (Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Container $container
     *
     * @return void
     * @throws \Throwable
     */
    public function provide (Container $container): void
    {
        try {
            $this->getRouter()->dispatchRequestURL();
        } catch (\Exception|\Throwable $e) {

            if ($e->getCode() === 404) {

                # Go-To Installer Page if 404 and tonics is not ready
                if (AppConfig::TonicsIsNotReady()) {
                    redirect(\route('admin.installer'));
                }

                $redirect_to = $this->tryURLRedirection();
                $reURL = url()->getRequestURL();
                if ($redirect_to === false) {
                    if (AppConfig::canLog404()) {
                        try {
                            db(onGetDB: function (TonicsQuery $db) use ($reURL) {
                                $db->Insert(
                                    Tables::getTable(Tables::BROKEN_LINKS),
                                    [
                                        'from' => $reURL,
                                        'to'   => null,
                                    ],
                                );
                            });
                        } catch (\Exception $exception) {
                            // Log..
                        }
                    }
                } else {
                    if (isset($redirect_to->to) && !empty($redirect_to->to)) {
                        redirect($redirect_to->to, $redirect_to->redirection_type);
                    } else {
                        if (!empty($reURL)) {
                            $hit = $redirect_to->hit ?? 1;
                            try {
                                db(onGetDB: function (TonicsQuery $db) use ($hit, $reURL) {
                                    $db->FastUpdate(
                                        Tables::getTable(Tables::BROKEN_LINKS),
                                        [
                                            '`from`' => $reURL,
                                            '`to`'   => null,
                                            '`hit`'  => ++$hit,
                                        ],
                                        db()->WhereEquals('`from`', $reURL),
                                    );
                                });
                            } catch (\Exception $exception) {
                                // Log..
                            }
                        }
                    }
                }
            }
            if (AppConfig::isProduction()) {
                SimpleState::displayErrorMessage($e->getCode(), $e->getMessage());
            } else {
                SimpleState::displayErrorMessage($e->getCode(), $e->getMessage() . $e->getTraceAsString());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function tryURLRedirection (): object|bool
    {
        try {
            $result = null;
            db(onGetDB: function (TonicsQuery $db) use (&$result) {
                $table = Tables::getTable(Tables::BROKEN_LINKS);
                $result = $db->Select('*')->From($table)->WhereEquals(table()->pickTable($table, ['from']), url()->getRequestURL())->FetchFirst();
            });
            if (is_object($result)) {
                return $result;
            }
        } catch (\Exception $exception) {
            // Log..
        }

        return false;
    }

    /**
     * @return Router
     */
    public function getRouter (): Router
    {
        return $this->router;
    }
}