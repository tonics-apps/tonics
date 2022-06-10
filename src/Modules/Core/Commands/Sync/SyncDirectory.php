<?php

namespace App\Modules\Core\Commands\Sync;

use App\Modules\Core\Commands\ClearCache;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * This is a one way synchronization, where files are copied from a source to a destination but no files would be copied from
 * a destination to a source.
 *
 * <br>
 * Syntax: php bin/console --sync --folders=src1,dest1,src2,dest2...
 *
 * <br>
 * If files in src1 differs from dest1 (or it doesn't exist), it would be copied to dest1, the same is true for src2, and dest2.
 */
class SyncDirectory implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            '--sync',
            "--folders",
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $folders = explode(',', $commandOptions['--folders']);
        if (count($folders) % 2 === 0){
            $this->infoMessage("Running Syncing");
            $destSrcs = array_chunk($folders, 2);
            $this->infoMessage("Watching For Changes");
            $cache = new ClearCache(); $helper = helper();
            while (true){
                foreach ($destSrcs as $destSrc){
                    $src = $destSrc[0];
                    $dest = $destSrc[1];
                    if ($helper->isDirectory($src) && $helper->isDirectory($dest)){
                        $srcHash = $helper->hashDirectory($src);
                        $destHash = $helper->hashDirectory($dest);
                       // $this->delayMessage("$srcHash - $destHash");
                        if ($srcHash !== $destHash){
                            $this->infoMessage("$src has been modified, updating $dest");
                            if ($helper->copyFolder($src, $dest)){
                                $this->successMessage(" `$src` successfully replicated to `$dest` ");
                                $cache->run([
                                    '--cache' => '',
                                    '--clear' => '',
                                    '--warm' => 0
                                ]);
                            } else {
                                $this->errorMessage("Failed replicating `$src` to `$dest`");
                                break;
                            }
                        }
                    } else {
                        $this->errorMessage("One of `$src` or `$dest` is not a directory");
                        break;
                    }
                }
            }
        } else {
            $this->errorMessage("--folders should be in format `--folders=src,dest,src2,dest2,...` one of src/dest shouldn't be omitted");
        }
    }
}