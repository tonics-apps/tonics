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

describe( "Tonics Helper", function () {

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
                'file' => $testFile,
                'hash' => 'sha384',
                'signatures' => [
                    ['key' => $publicKey, 'sig' => $sig]
                ]
            ]);

            expect($verify)->toBeTruthy();
        });

        it("should return true", function () use ($sig, $publicKey, $testFile, $helper) {
            $verify = $helper->signingVerifyFileSignature([
                'file' => $testFile,
                'signatures' => [
                    ['key' => $publicKey, 'sig' => $sig]
                ]
            ]);
            expect($verify)->toBeTruthy();
        });

        it("should throw exception with no valid sig message", function () use ($publicKey, $testFile, $helper) {
            $closure = function () use ($helper, $publicKey, $testFile) {
                $helper->signingVerifyFileSignature([
                    'file' => $testFile,
                    'hash' => 'sha384',
                    'signatures' => [
                        ['key' => $publicKey, 'sig' => 'xxx']
                    ]
                ]);
            };
            $fileName = $helper->getFileName($testFile);
            expect($closure)->toThrow(new Exception("The authenticity of $fileName could not be verified as no valid signature was found."));
        });

        it("should throw exception with no valid sig message as the file is not signed by the key", function () use ($sig, $publicKey, $helper) {
            $closure = function () use ($sig, $helper, $publicKey) {
                $helper->signingVerifyFileSignature([
                    'file' => __FILE__,
                    'hash' => 'sha384',
                    'signatures' => [
                        ['key' => $publicKey, 'sig' => $sig]
                    ]
                ]);
            };
            $fileName = $helper->getFileName(__FILE__);
            expect($closure)->toThrow(new Exception("The authenticity of $fileName could not be verified as no valid signature was found."));
        });

    });
});

