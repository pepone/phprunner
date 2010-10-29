// *****************************************************************************
//
// Copyright (c) 2010 José Manuel, Gutiérrez de la Concha. All rights reserved.
//
// This copy of PhpRunner is licensed to you under the terms described
// in the LICENSE file included in this distribution.
//
// email: pepone.onrez@gmail.com
//
// *****************************************************************************

#ifndef OZ_PHP_COMMAND_RUNNER_ICE
#define OZ_PHP_COMMAND_RUNNER_ICE

#include <Ice/BuiltinSequences.ice>
module oz
{

dictionary <string, string> StringMap;

module php
{

/**
 *
 * This exception indicate that the scipt doesn't exists on this
 * system.
 *
 **/
exception CommandNotExistsException
{
    string path;
};

exception CommandNotRunningException
{
    string id;
};

exception ArgumentException
{
    string reason;
};

struct ProcessInfo
{
    string id;
    string script;
    Ice::StringSeq args;
    long timestamp;
};
sequence<ProcessInfo> ProcessInfoSeq;

/**
 *
 * Interface to execute commands.
 *
 **/
interface CommandRunner
{
    string execute(string command, Ice::StringSeq args) throws CommandNotExistsException, ArgumentException;
    bool isRunning(string id) throws ArgumentException;
    void stop(string id) throws CommandNotRunningException, ArgumentException;
    ProcessInfoSeq processList();
};

};

};
#endif
