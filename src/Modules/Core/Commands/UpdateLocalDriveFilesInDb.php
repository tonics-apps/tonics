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

namespace App\Modules\Core\Commands;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\Exceptions\FileException;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsFileManager\Utilities\FileHelper;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class UpdateLocalDriveFilesInDb
 * e.g to update files in db run:  php bin/console --run --preinstall=update:drivelocal:db
 * @package App\Modules\Core\Commands
 */
class UpdateLocalDriveFilesInDb implements ConsoleCommand
{
    use ConsoleColor, FileHelper;

    private string $path;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->path = DriveConfig::getUploadsPath();
        set_time_limit(0);
        // Depending on the files on your drive, it might require a lot of memory
        ini_set('memory_limit','-1');
    }

    public function required(): array
    {
        return [
            "update:drivelocal:db"
        ];
    }

    /**
     * @param array $commandOptions
     * @return void
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        if (!helper()->fileExists(DriveConfig::getUploadsPath()) && !helper()->fileExists(DriveConfig::getPrivatePath())){
            $this->errorMessage("Drive Uploads or Private Path Doesn't Exist");
            return;
        }

        db(onGetDB: function ($db){
            $tbl = Tables::getTable(Tables::DRIVE_SYSTEM);
            $files = $this->scanAndProcessFiles($this->getPath());
            $db->insertOnDuplicate(
                table: $tbl, data: $files, update: ['drive_id', 'drive_parent_id', 'type', 'filename', 'properties'], chunkInsertRate: 2000
            );
        });
    }

    /**
     * @param $drivePath
     * @return array
     * @throws \Exception
     */
    public function scanAndProcessFiles($drivePath): array
    {
        $filesAndDir = []; $drive_name = 'local';

        # Storing it in a variable avoid having to call AppConfig::initLoader(),
        # in the worst scenario, it won't even call it from cache, so, to be safe, we store it in a variable
        $helper = helper();
        $relPath = $helper->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), $drivePath);
        if ($relPath === false){
            $this->errorMessage('An Error Getting The DriveSystem Relative Signature');
            exit(1);
        }
        $initFolder = new \SplFileInfo($drivePath);
        $filesAndDir[$relPath] = [
            "drive_id" => 1,
            "drive_parent_id" => null,
            "drive_unique_id" => hash('sha256',$relPath . random_int(0000000, PHP_INT_MAX)),
            "drive_name" => $drive_name,
            "type" => "directory",
            'filename' => $initFolder->getFilename(),
            "properties" => json_encode([
                'ext' => null,
                'filename' => $initFolder->getFilename(),
                'size' => $initFolder->getSize(),
                "time_created" => $initFolder->getCTime(),
                "time_modified" => $initFolder->getMTime()
            ]),
            "security" => json_encode([
                "lock" => false,
                "password" => 1 . random_int(0000000, PHP_INT_MAX),
            ])
        ];

        $i = 2;
        foreach ($helper->recursivelyScanDirectory($drivePath) as $file){

            /**@var $file \SplFileInfo */
            $filename = $file->getFilename();
            $pathname = $file->getPathname();
            $timeModified = $file->getMTime();
            $timeCreated = $file->getCTime();
            $size = $file->getSize();
            $ext = $file->getExtension() ?? null;

            if ($file->isDir()){
                $parentSignature = $helper->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $file->getRealPath());

                #
                # Each file uses the relative_path e.g "~/uploads/audio" as its key, the good thing about doing things this way is
                # we can easily get the parent_id of a file by using the parentSignature e.g "~/uploads" to find the relative_path key,
                # this is what the this->findFileParent($filesAndDir, $parentSignature) does ;)
                #
                # The full-path can be constructed by using cte
                #
                $relPath = $helper->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), $file->getRealPath());
                $uniqueID = hash('sha256',$relPath . random_int(0000000, PHP_INT_MAX));

                $filesAndDir[$relPath] = [
                    // the drive_id must be a unique hash since we can only have a single unique rel-path
                    // "drive_id" => $this->hashToDecimal($relPath),
                    "drive_id" => $i,
                    "drive_parent_id" => $this->findFileParent($filesAndDir, $parentSignature),
                    // "drive_unique_id" => $this->hashToDecimal($relPath),
                    "drive_unique_id" => $uniqueID,
                    "drive_name" => $drive_name,
                    "type" => "directory",
                    'filename' => $filename,
                    "properties" => json_encode([
                        'ext' => null,
                        'filename' => $filename,
                        'size' => $size,
                        "time_created" => $timeCreated,
                        "time_modified" => $timeModified
                    ]),
                    "security" => json_encode([
                        "lock" => false,
                        "password" => $i.random_int(0000000, PHP_INT_MAX),
                    ])
                ];
            }

            if ($file->isFile()){
                $parentSignature = $helper->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $pathname);
                $relPath = $helper->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), $pathname);
                $uniqueID = hash('sha256', $relPath . random_int(0000000, PHP_INT_MAX));
                $properties = [
                    'ext' => $ext,
                    'filename' => $filename,
                    'size' => $size,
                    "time_created" => $timeCreated,
                    "time_modified" => $timeModified
                ];
                $helper->moreFileProperties($file->getRealPath(), $ext, $properties);
                $filesAndDir[$relPath] = [
                    // the drive_id must be a unique hash since we can only have a single unique rel-path
                    // "drive_id" => $this->hashToDecimal($relPath),
                    "drive_id" => $i,
                    "drive_parent_id" => $this->findFileParent($filesAndDir, $parentSignature),
                    // "drive_unique_id" => $this->hashToDecimal($relPath),
                    "drive_unique_id" => $uniqueID,
                    "drive_name" => $drive_name,
                    "type" => "file",
                    'filename' => $filename,
                    "properties" => json_encode($properties),
                    "security" => json_encode([
                        "lock" => false,
                        "password" => $i.random_int(0000000, PHP_INT_MAX),
                    ])
                ];
            }
            ++$i;
        }
        // return array_values($filesAndDir);
        return $filesAndDir;
    }

    /**
     * @param array $filesAndDir
     * @param string $parentSignature
     * @return mixed
     * @throws \Exception
     */
    #[ArrayShape(['drive_parent_id' => "mixed", 'drive_unique_path' => "string"])] public function findFileParent(array &$filesAndDir, string $parentSignature): mixed
    {

        if (!key_exists($parentSignature, $filesAndDir)){
            throw new FileException("Can't Find Parent `$parentSignature`");
        } else {
            return $filesAndDir[$parentSignature]['drive_id'];
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}