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

namespace App\Modules\Core\States;

use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

/**
 * Common in the sense that most resource is of the format resource-name/slug-id/slug,
 * so, we can leverage this phenomenon for similar ones when redirecting...
 */
class CommonResourceRedirection extends SimpleState
{
    private OnRequestProcess $request;
    private string $intendedPostURL = '';
    private string $slugID = '';
    /**
     * @var callable
     */
    private $onSlugIDCallable;
    /**
     * @var callable
     */
    private $onSlugCallable;
    /**
     * @var callable|null
     */
    private $onResourceErrorState;

    /**
     * @throws \Exception
     */
    public function __construct(callable $onSlugIDState, callable $onSlugState, callable $onResourceErrorState = null){
        $this->onSlugIDCallable = $onSlugIDState;
        $this->onSlugCallable = $onSlugState;
        $this->onResourceErrorState = $onResourceErrorState;
        $this->request = request();
        $this->setCurrentState(self::OnInitialState);
    }

    # States For PostRedirection
    const OnInitialState = 'OnInitialState';
    const OnSlugUniqueIDState = 'OnSlugUniqueIDState';
    const OnStringIDState = 'OnStringIDState';
    const OnRedirectToIntendedState = 'OnRedirectToIntendedState';
    const OnResourceErrorState = 'OnResourceErrorState';

    public function OnInitialState(): string
    {
        $slugID = $this->getRequest()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0];
        $this->setSlugID($slugID);

        ## Assume SlugUniqueID since it's a hash with 16 chars
        if (strlen($slugID) === 16){
            return $this->switchState(self::OnSlugUniqueIDState, self::NEXT);
        }
        return $this->switchState(self::OnStringIDState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function OnSlugUniqueIDState(): string
    {
        try {
            $callable = $this->onSlugIDCallable;
            $resource = $callable($this->getSlugID());
            if ($resource){
                $this->intendedPostURL = $resource;
                return $this->switchState(self::OnRedirectToIntendedState, self::NEXT);
            }
            # If the above fails, then it could be that the actual slug has a len of 16, so, let's go check it
            return $this->switchState(self::OnStringIDState, self::NEXT);

        } catch (\Exception $exception){
            ## Okay, if postID contains a dash, then we guess wrong, it is probably a literal post_slug
            if (str_contains('-', $this->getSlugID())){
                return $this->switchState(self::OnStringIDState, self::NEXT);
            }
            return $this->switchState(self::OnResourceErrorState, self::NEXT);
        }
    }

    public function OnStringIDState(): string
    {
        try {
            $callable = $this->onSlugCallable;
            $resource = $callable($this->getSlugID());
            if ($resource){
                $this->intendedPostURL = $resource;
                return $this->switchState(self::OnRedirectToIntendedState, self::NEXT);
            }
            return $this->switchState(self::OnResourceErrorState, self::NEXT);
        } catch (\Exception $exception){
            return $this->switchState(self::OnResourceErrorState, self::NEXT);
        }
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnRedirectToIntendedState()
    {
        redirect($this->intendedPostURL);
    }

    /**
     * @throws \Exception
     */
    public function OnResourceErrorState()
    {
        if ($this->onResourceErrorState){
            $callable = $this->onResourceErrorState;
            $callable();
        } else{
            throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
        }
    }
    /**
     * @return OnRequestProcess|null
     */
    public function getRequest(): ?OnRequestProcess
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getIntendedPostURL(): string
    {
        return $this->intendedPostURL;
    }


    /**
     * @return string
     */
    public function getSlugID(): string
    {
        return $this->slugID;
    }

    /**
     * @param string $slugID
     */
    public function setSlugID(string $slugID): void
    {
        $this->slugID = $slugID;
    }

    /**
     * @param OnRequestProcess|null $request
     * @return $this
     */
    public function setRequest(?OnRequestProcess $request): static
    {
        $this->request = $request;
        return $this;
    }

}