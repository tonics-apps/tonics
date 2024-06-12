<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

use Devsrealm\TonicsHelpers\TonicsHelpers;

describe("Tonics Helper", function () {

    /** @var TonicsHelpers $helper */
    $helper = $this->helper;

    describe("->extractNameFromEmail();", function () use ($helper) {

        it("should return the local part of a valid email address", function () use ($helper) {
            $emailAddress = "john.doe@example.com";
            $result = $helper->extractNameFromEmail($emailAddress);
            expect($result)->toEqual("john.doe");
        });

        it("should return null for an invalid email address", function () use ($helper) {
            $invalidEmailAddress = "john.doe@com";
            $result = $helper->extractNameFromEmail($invalidEmailAddress);
            expect($result)->toBeNull();
        });

        it("should return null for an empty email address", function () use ($helper) {
            $emptyEmailAddress = "";
            $result = $helper->extractNameFromEmail($emptyEmailAddress);
            expect($result)->toBeNull();
        });

        it("should return the local part of an email address with subdomains", function () use ($helper) {
            $emailAddress = "jane.doe@mail.example.com";
            $result = $helper->extractNameFromEmail($emailAddress);
            expect($result)->toEqual("jane.doe");
        });

        it("should return the local part of an email address with a numeric domain", function () use ($helper) {
            $emailAddress = "user123@123domain.com";
            $result = $helper->extractNameFromEmail($emailAddress);
            expect($result)->toEqual("user123");
        });

        it("should return the local part of an email address with special characters", function () use ($helper) {
            $emailAddress = "user.name+tag+sorting@example.com";
            $result = $helper->extractNameFromEmail($emailAddress);
            expect($result)->toEqual("user.name+tag+sorting");
        });
    });

    describe("File and Directory Helper Functions", function () use ($helper) {

        describe("->isDirectory();", function () use ($helper) {

            it("should return true for a valid directory", function () use ($helper) {
                $directory = __DIR__;
                expect($helper->isDirectory($directory))->toBeTruthy();
            });

            it("should return false for a valid file", function () use ($helper) {
                $file = __FILE__;
                expect($helper->isDirectory($file))->toBeFalsy();
            });

            it("should return false for a non-existing directory", function () use ($helper) {
                $directory = __DIR__ . DIRECTORY_SEPARATOR . 'nonexistent';
                expect($helper->isDirectory($directory))->toBeFalsy();
            });
        });

        describe("->isFile();", function () use ($helper) {

            it("should return true for a valid file", function () use ($helper) {
                $file = __FILE__;
                $result = $helper->isFile($file);
                expect($result)->toBe(true);
            });

            it("should return false for a valid directory", function () use ($helper) {
                $directory = __DIR__;
                expect($helper->isFile($directory))->toBeFalsy();
            });

            it("should return false for a non-existing file", function () use ($helper) {
                $file = __DIR__ . DIRECTORY_SEPARATOR . 'nonexistentfile.txt';
                expect($helper->isFile($file))->toBeFalsy();
            });
        });

        describe("->fileExists();", function () use ($helper) {

            it("should return true for an existing file", function () use ($helper) {
                $file = __FILE__;
                expect($helper->fileExists($file))->toBeTruthy();
            });

            it("should return true for an existing directory", function () use ($helper) {
                $directory = __DIR__;
                expect($helper->fileExists($directory))->toBeTruthy();
            });

            it("should return false for a non-existing path", function () use ($helper) {
                $path = __DIR__ . DIRECTORY_SEPARATOR . 'nonexistentpath';
                expect($helper->fileExists($path))->toBeFalsy();
            });
        });

        describe("->missing();", function () use ($helper) {

            it("should return false for an existing file", function () use ($helper) {
                $file = __FILE__;
                expect($helper->missing($file))->toBeFalsy();
            });

            it("should return false for an existing directory", function () use ($helper) {
                $directory = __DIR__;
                expect($helper->missing($directory))->toBeFalsy();
            });

            it("should return true for a non-existing path", function () use ($helper) {
                $path = __DIR__ . DIRECTORY_SEPARATOR . 'nonexistentpath';
                expect($helper->missing($path))->toBeTruthy();
            });
        });
    });

    describe("->signingVerifyFileSignature();", function () use ($helper) {

        $testFile = __DIR__ . DIRECTORY_SEPARATOR . 'TestFile.zip';
        $publicKey = "WY/nS1Z/HU9YpmJVzI94ryBkxSe+KTRBzFkciUS6a5g";
        $sig = "tX27yrO8Dz5EcN9fJONvfrLgIvgS2AsqGg1N8Q1atBrpYCSgJUfwJYhiRcvewEj5qcvMLVHtfLLVkGqm5ZfgCA";

        it("should return true", function () use ($sig, $publicKey, $testFile, $helper) {

            $verify = $helper->signingVerifyFileSignature([
                'file'       => $testFile,
                'hash'       => 'sha384',
                'signatures' => [
                    ['key' => $publicKey, 'sig' => $sig],
                ],
            ]);

            expect($verify)->toBeTruthy();
        });

        it("should return true", function () use ($sig, $publicKey, $testFile, $helper) {
            $verify = $helper->signingVerifyFileSignature([
                'file'       => $testFile,
                'signatures' => [
                    ['key' => $publicKey, 'sig' => $sig],
                ],
            ]);
            expect($verify)->toBeTruthy();
        });

        it("should throw exception with no valid sig message", function () use ($publicKey, $testFile, $helper) {
            $closure = function () use ($helper, $publicKey, $testFile) {
                $helper->signingVerifyFileSignature([
                    'file'       => $testFile,
                    'hash'       => 'sha384',
                    'signatures' => [
                        ['key' => $publicKey, 'sig' => 'xxx'],
                    ],
                ]);
            };
            $fileName = $helper->getFileName($testFile);
            expect($closure)->toThrow(new Exception("The authenticity of $fileName could not be verified as no valid signature was found."));
        });

        it("should throw exception with no valid sig message as the file is not signed by the key", function () use ($sig, $publicKey, $helper) {
            $closure = function () use ($sig, $helper, $publicKey) {
                $helper->signingVerifyFileSignature([
                    'file'       => __FILE__,
                    'hash'       => 'sha384',
                    'signatures' => [
                        ['key' => $publicKey, 'sig' => $sig],
                    ],
                ]);
            };
            $fileName = $helper->getFileName(__FILE__);
            expect($closure)->toThrow(new Exception("The authenticity of $fileName could not be verified as no valid signature was found."));
        });

    });

    describe('VersionChecker', function () use ($helper) {

        describe('->versionPHPUptoVersion();', function () use ($helper) {

            it('should return true if the PHP version is greater than the specified version', function () use ($helper) {
                expect($helper->versionPHPUptoVersion('7.3.0', '7.4.0'))->toBeTruthy();
            });

            it('should return true if the PHP version is equal the specified version', function () use ($helper) {
                expect($helper->versionPHPUptoVersion('7.3.0', '7.3.0'))->toBeTruthy();
            });

            it('should return false if the PHP version is less than the specified version', function () use ($helper) {
                expect($helper->versionPHPUptoVersion('7.4.0', '7.3.0'))->toBeFalsy();
            });
        });

        describe('->versionPHPLessThan();', function () use ($helper) {

            it('should return true if the version is less than the target version', function () use ($helper) {
                expect($helper->versionPHPLessThan('7.3.0', '7.4.0'))->toBeTruthy();
            });

            it('should return false if the version is greater than or equal to the target version', function () use ($helper) {
                expect($helper->versionPHPLessThan('7.4.0', '7.3.0'))->toBeFalsy();
            });
        });

        describe('->versionPHPGreaterThan();', function () use ($helper) {

            it('should return true if the version is greater than the target version', function () use ($helper) {
                expect($helper->versionPHPGreaterThan('7.4.0', '7.3.0'))->toBeTruthy();
            });

            it('should return false if the version is less than or equal to the target version', function () use ($helper) {
                expect($helper->versionPHPGreaterThan('7.3.0', '7.4.0'))->toBeFalsy();
            });

            it('should return false if the version equal the target version', function () use ($helper) {
                expect($helper->versionPHPGreaterThan('7.4.0', '7.4.0'))->toBeFalsy();
            });
        });

        describe('->versionPHPEqual();', function () use ($helper) {

            it('should return true if the version is equal to the target version', function () use ($helper) {
                expect($helper->versionPHPEqual('7.3.0', '7.3.0'))->toBeTruthy();
            });

            it('should return false if the version is not equal to the target version', function () use ($helper) {
                expect($helper->versionPHPEqual('7.4.0', '7.3.0'))->toBeFalsy();
            });
        });

        describe('->versionPHPAtLeast();', function () use ($helper) {

            it('should return true if the version is at least as high as the target version', function () use ($helper) {
                expect($helper->versionPHPAtLeast('7.3.0', '7.4.0'))->toBeTruthy();
            });

            it('should return false if the version is lower than the target version', function () use ($helper) {
                expect($helper->versionPHPAtLeast('7.4.0', '7.3.0'))->toBeFalsy();
            });

        });
    });

    describe("->updateEnvValue();", function () use ($helper) {

        function createTempEnvFile (string $content = ""): string
        {
            $tempFile = tempnam(sys_get_temp_dir(), 'env');
            file_put_contents($tempFile, $content);
            return $tempFile;
        }

        function deleteTempEnvFile (string $tempFile): void
        {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        it("should throw an exception if the environment file does not exist", function () use ($helper) {
            $nonExistentPath = "/path/to/nonexistent/.env";
            $envKey = "NEW_KEY";
            $envValue = "NEW_VALUE";

            expect(function () use ($helper, $nonExistentPath, $envKey, $envValue) {
                $helper->updateEnvValue($nonExistentPath, $envKey, $envValue);
            })->toThrow(new InvalidArgumentException("The environment file '$nonExistentPath' does not exist."));
        });

        it("should append a new key-value pair if the key does not exist", function () use ($helper) {
            $envContent = "EXISTING_KEY=EXISTING_VALUE\n";
            $envPath = createTempEnvFile($envContent);
            $envKey = "NEW_KEY";
            $envValue = "NEW_VALUE";

            $helper->updateEnvValue($envPath, $envKey, $envValue);

            $updatedContent = file_get_contents($envPath);
            expect($updatedContent)->toContain("NEW_KEY=NEW_VALUE");

            deleteTempEnvFile($envPath);
        });

        it("should update the value of an existing key", function () use ($helper) {
            $envContent = "EXISTING_KEY=OLD_VALUE\n";
            $envPath = createTempEnvFile($envContent);
            $envKey = "EXISTING_KEY";
            $envValue = "UPDATED_VALUE";

            $helper->updateEnvValue($envPath, $envKey, $envValue);

            $updatedContent = file_get_contents($envPath);
            expect($updatedContent)->toContain("EXISTING_KEY=UPDATED_VALUE");
            expect($updatedContent)->not->toContain("EXISTING_KEY=OLD_VALUE");

            deleteTempEnvFile($envPath);
        });

        it("should handle an environment file with different line endings", function () use ($helper) {
            $envContent = "EXISTING_KEY=OLD_VALUE\r\n";
            $envPath = createTempEnvFile($envContent);
            $envKey = "EXISTING_KEY";
            $envValue = "UPDATED_VALUE";

            $helper->updateEnvValue($envPath, $envKey, $envValue);

            $updatedContent = file_get_contents($envPath);
            expect($updatedContent)->toContain("EXISTING_KEY=UPDATED_VALUE");
            expect($updatedContent)->not->toContain("EXISTING_KEY=OLD_VALUE");

            deleteTempEnvFile($envPath);
        });

        it("should handle an empty environment file", function () use ($helper) {
            $envPath = createTempEnvFile();
            $envKey = "NEW_KEY";
            $envValue = "NEW_VALUE";

            $helper->updateEnvValue($envPath, $envKey, $envValue);

            $updatedContent = file_get_contents($envPath);
            expect($updatedContent)->toEqual("NEW_KEY=NEW_VALUE\n");

            deleteTempEnvFile($envPath);
        });

        it("should throw an exception if it fails to read the environment file", function () use ($helper) {
            $envContent = "EXISTING_KEY=EXISTING_VALUE\n";
            $envPath = createTempEnvFile($envContent);
            chmod($envPath, 0222); // Write-only permission
            $envKey = "NEW_KEY";
            $envValue = "NEW_VALUE";

            expect(function () use ($helper, $envPath, $envKey, $envValue) {
                $helper->updateEnvValue($envPath, $envKey, $envValue);
            })->toThrow(new \RuntimeException("The environment file '{$envPath}' is not readable."));

            chmod($envPath, 0644); // Restore permissions so we can delete it
            deleteTempEnvFile($envPath);
        });

        it("should throw an exception if it fails to write the environment file", function () use ($helper) {
            $envContent = "EXISTING_KEY=EXISTING_VALUE\n";
            $envPath = createTempEnvFile($envContent);
            chmod($envPath, 0444); // Read-only permission
            $envKey = "NEW_KEY";
            $envValue = "NEW_VALUE";

            expect(function () use ($helper, $envPath, $envKey, $envValue) {
                $helper->updateEnvValue($envPath, $envKey, $envValue);
            })->toThrow(new \RuntimeException("The environment file '{$envPath}' is not writable."));

            chmod($envPath, 0644); // Restore permissions so we can delete it
            deleteTempEnvFile($envPath);
        });

        it("should add multiple keys to an empty environment file", function () use ($helper) {
            $envPath = createTempEnvFile(); // Empty environment file
            $envValues = [
                "KEY1" => "VALUE1",
                "KEY2" => "VALUE2",
                "KEY3" => "VALUE3",
            ];

            foreach ($envValues as $key => $value) {
                $helper->updateEnvValue($envPath, $key, $value);
            }

            $updatedContent = file_get_contents($envPath);
            foreach ($envValues as $key => $value) {
                expect($updatedContent)->toContain("{$key}={$value}");
            }

            deleteTempEnvFile($envPath);
        });

        it("should update multiple keys in an existing environment file", function () use ($helper) {
            $envContent = "KEY1=OLD_VALUE1\nKEY2=OLD_VALUE2\nKEY3=OLD_VALUE3\n";
            $envPath = createTempEnvFile($envContent);
            $envValues = [
                "KEY1" => "NEW_VALUE1",
                "KEY2" => "NEW_VALUE2",
                "KEY3" => "NEW_VALUE3",
            ];

            foreach ($envValues as $key => $value) {
                $helper->updateEnvValue($envPath, $key, $value);
            }

            $updatedContent = file_get_contents($envPath);
            foreach ($envValues as $key => $value) {
                expect($updatedContent)->toContain("{$key}={$value}");
            }

            deleteTempEnvFile($envPath);
        });

        it("should add new keys and update existing keys in an existing environment file", function () use ($helper) {
            $envContent = "KEY1=OLD_VALUE1\n";
            $envPath = createTempEnvFile($envContent);
            $envValues = [
                "KEY1" => "NEW_VALUE1",
                "KEY2" => "VALUE2",
                "KEY3" => "VALUE3",
            ];

            foreach ($envValues as $key => $value) {
                $helper->updateEnvValue($envPath, $key, $value);
            }

            $updatedContent = file_get_contents($envPath);
            foreach ($envValues as $key => $value) {
                expect($updatedContent)->toContain("{$key}={$value}");
            }

            deleteTempEnvFile($envPath);
        });

    });

});

