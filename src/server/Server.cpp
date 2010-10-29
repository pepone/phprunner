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

#include <string>
#include <Ice/Ice.h>
#include <Ice/Service.h>
#include <IceUtil/IceUtil.h>
#include <IceUtil/FileUtil.h>
#include <PhpRunner.h>
#include <QProcess>

using namespace std;
using namespace oz::php;

bool endsWith(const string& str, const string& key)
{
    if(str.length() > key.length())
    {
        return (0 == str.compare(str.length() - key.length(), key.length(), key));
    }
    return false;
}


class CommandPool : virtual public IceUtil::Shared
{
public:

    virtual void removeTask(const string&) = 0;
};

typedef IceUtil::Handle<CommandPool> CommandPoolPtr;

class Task : public IceUtil::Thread
{
public:
        
    Task(const CommandPoolPtr& pool, const string& id, const string& command,
         const Ice::StringSeq& args, const Ice::LoggerPtr& logger, int logLevel) :
        _process(new QProcess()),
        _pool(pool),
        _id(id),
        _command(command),
        _args(args),
        _logger(logger),
        _logLevel(logLevel),
        _timestamp(IceUtil::Time::now().toMilliSeconds())
    {
    }

    ~Task()
    {
        delete(_process);
    }

    virtual void
    run()
    {
        QStringList args;
        for(Ice::StringSeq::const_iterator i = _args.begin(); i != _args.end(); ++i)
        {
            args << i->c_str();
        }

        _process->start(_command.c_str(), args);
        if(_logLevel > 0)
        {
            ostringstream os;
            os << "starting " << _command << " ";
            os << args.join(" ").toUtf8().data() << " ";

            _logger->trace("info", os.str());
        }

        if(!_process->waitForStarted(-1))
        {
            if(_logLevel > 0)
            {
                ostringstream os;
                os << "command failed to start id `" << _id << "' ";
                os << "error: `" << _process->errorString().toUtf8().data() << "' ";
                os << "executable: `" << _command << "' ";
                os << "arguments `(" << args.join(";").toUtf8().data() << ")' ";
                os << "exit code: `" << _process->exitCode() << "'";
                
                QByteArray result = _process->readAll();
                os << "\nout:[";
                if(result.length() > 0)
                {
                    os << "\n" << result.data() << "\n";
                }
                os << "]";

                result = _process->readAllStandardError();
                os << "\nerr:[";
                if(result.length() > 0)
                {
                    os << "\n" << result.data() << "\n";
                }
                os << "]";
                _logger->trace("error", os.str());
            }
            _pool->removeTask(_id);
            return;
        }
        if(!_process->waitForFinished(-1))
        {
            if(_logLevel > 0)
            {
                ostringstream os;
                os << "command falied to finish id `" << _id << "' ";
                os << "error: `" << _process->errorString().toUtf8().data() << "' ";
                os << "executable: `" << _command << "' ";
                os << "arguments `(" << args.join(";").toUtf8().data() << ")' ";
                os << "exit code: `" << _process->exitCode() << "'";

                QByteArray result = _process->readAll();
                os << "\nout:[";
                if(result.length() > 0)
                {
                    os << "\n" << result.data() << "\n";
                }
                os << "]";

                result = _process->readAllStandardError();
                os << "\nerr:[";
                if(result.length() > 0)
                {
                    os << "\n" << result.data() << "\n";
                }
                os << "]";
                _logger->trace("error", os.str());
            }
            _pool->removeTask(_id);
            return;
        }

        if(_logLevel > 0)
        {

            ostringstream os;
            os << "command success id `" << _id << "' ";
            os << "executable: `" << _command << "' ";
            os << "arguments `(" << args.join(";").toUtf8().data() << ")' ";
            os << "exit code: `" << _process->exitCode() << "'";

            QByteArray result = _process->readAll();
            os << "\nout:[";
            if(result.length() > 0)
            {
                os << "\n" << result.data() << "\n";
            }
            os << "]";

            result = _process->readAllStandardError();
            os << "\nerr:[";
            if(result.length() > 0)
            {
                os << "\n" << result.data() << "\n";
            }
            os << "]";
            _logger->trace("info", os.str());
        }

       _pool->removeTask(_id);
    }

    void
    stop()
    {
        if(_logLevel > 0)
        {
            ostringstream os;
            os << "command close id `" << _id << "' ";
            os << "executable: `" << _command << "' ";
            _logger->trace("info", os.str());
        }
        _process->kill();
    }

    ProcessInfo
    info()
    {
        ProcessInfo inf;
        inf.id = _id;
        if(_args.size() > 0)
        {
            inf.script = _args.front();
            inf.args = _args;
            inf.args.erase(inf.args.begin());
        }
        inf.timestamp = _timestamp;
        return inf;
    }

private:

    QProcess* _process;
    CommandPoolPtr _pool;
    string _id;
    string _command;
    Ice::StringSeq _args;
    Ice::LoggerPtr _logger;
    int _logLevel;
    IceUtil::Int64 _timestamp;
};
typedef IceUtil::Handle<Task> TaskPtr;

class CommandRunnerI : virtual public CommandRunner, virtual public CommandPool
{
public:
    
    CommandRunnerI(const Ice::LoggerPtr& logger, const string& phpExecutable,
        const string& scriptPath, int logLevel) :
        _logger(logger),
        _phpExecutable(phpExecutable),
        _scriptPath(scriptPath),
        _logLevel(logLevel)
    {
    }
    
    virtual string
    execute(const string& name, const Ice::StringSeq& args, const Ice::Current&)
    {
        if(name.find("..", 0) != string::npos)
        {
            ostringstream os;
            os << "script path cannot contain '..' characters used to denote relative paths, path: `"
               << name << "'";
            ArgumentException ex(os.str());
            throw ex;
        }
        if(_logLevel > 0)
        {
            ostringstream os;
            os << "execute script: `" << name << "'";
            _logger->trace("info", os.str());
        }
        string id = IceUtil::generateUUID();
        

        ostringstream script;
        script  << _scriptPath;
        
        if(!endsWith(_scriptPath, "/"))
        {
            script << "/";
        }
        script << name;

        if(!IceUtilInternal::fileExists(script.str()))
        {
            if(_logLevel > 0)
            {
                ostringstream os;
                os << "script `" << script.str() << "' not exists";
                _logger->trace("error", os.str());
            }
            CommandNotExistsException ex(script.str());
            throw ex;
        }
        vector<string> newArgs;
        newArgs.push_back(script.str());
        for(Ice::StringSeq::const_iterator i = args.begin(); i != args.end(); ++i)
        {
            newArgs.push_back(*i);
        }
        
        TaskPtr task = new Task(this, id, _phpExecutable, newArgs, _logger, _logLevel);

        {
            IceUtil::Mutex::Lock sync(_mutex);
            _tasks[id] = task;
        }
        task->start();
        return id;
    }
    
    virtual bool
    isRunning(const string& id, const Ice::Current&)
    {
        IceUtil::Mutex::Lock sync(_mutex);
        map< string, TaskPtr>::const_iterator i = _tasks.find(id);
        if(i != _tasks.end())
        {
            return true;
        }
        return false;
    }

    virtual void
    stop(const string& id, const Ice::Current&)
    {
        TaskPtr task;
        {
            IceUtil::Mutex::Lock sync(_mutex);
            map< string, TaskPtr>::iterator i = _tasks.find(id);
            if(i != _tasks.end())
            {
                task = i->second;
            }
        }
        if(task)
        {
            task->stop();
        }
        else
        {
            CommandNotRunningException ex(id);
            throw ex;
        }

        {
            IceUtil::Mutex::Lock sync(_mutex);
            map< string, TaskPtr>::iterator i = _tasks.find(id);
            if(i != _tasks.end())
            {
                _tasks.erase(i);
            }
        }
    }

    virtual void
    removeTask(const string& id)
    {
        IceUtil::Mutex::Lock sync(_mutex);
        map< string, TaskPtr>::iterator i = _tasks.find(id);
        if(i != _tasks.end())
        {
            _tasks.erase(i);
            if(_logLevel > 0)
            {
                ostringstream os;
                os << "remove task id `" << id << "'";
                _logger->trace("info", os.str());
            }
        }
    }

    virtual ProcessInfoSeq
    processList(const Ice::Current&)
    {
        ProcessInfoSeq processList;
        IceUtil::Mutex::Lock sync(_mutex);
        for(map< string, TaskPtr>::const_iterator i = _tasks.begin(); i != _tasks.end(); ++i)
        {
            processList.push_back(i->second->info());
        }
        return processList;
    }

    void
    stop()
    {
        IceUtil::Mutex::Lock sync(_mutex);
        for(map< string, TaskPtr>::const_iterator i = _tasks.begin(); i != _tasks.end(); ++i)
        {
            i->second->stop();
        }
        _tasks.clear();
    }
    
private:

    IceUtil::Mutex _mutex;
    map< string, TaskPtr> _tasks;
    Ice::LoggerPtr _logger;
    string _phpExecutable;
    string _scriptPath;
    int _logLevel;
};
typedef IceUtil::Handle<CommandRunnerI> CommandRunnerIPtr;

class Server : public Ice::Service
{

public:
    Server()
    {
    }
        
    virtual bool
    start(int, char**, int& status)
    {
        Ice::LoggerPtr logger = communicator()->getLogger();
        Ice::PropertiesPtr properties = communicator()->getProperties();
        int logLevel = properties->getPropertyAsIntWithDefault("log.level", 0);
        
        //
        // Path to php executable
        //
        string phpExecutable = properties->getPropertyWithDefault("php.executable", "/usr/bin/php");
        if(!IceUtilInternal::fileExists(phpExecutable))
        {
            throw string("PHP executable not found in: `"+ phpExecutable + "'");
        }
        if(logLevel > 0)
        {
            ostringstream os;
            os << "Using php executable from: `" << phpExecutable << "'";
            logger->trace("info", os.str());
        }
        
        //
        // Base directory to look for scripts
        //
        string scriptPath = properties->getProperty("script.path");
        if(!IceUtilInternal::directoryExists(scriptPath))
        {
            throw string("script.path: `" + scriptPath + "' doesn't point to a existing directory");
        }
        if(logLevel > 0)
        {
            ostringstream os;
            os << "Script path is: `" << scriptPath << "'";
            logger->trace("info", os.str());
        }

        _adapter = communicator()->createObjectAdapter("PhpRunner");
        _runner = new CommandRunnerI(logger, phpExecutable, scriptPath, logLevel);
        _adapter->add(_runner, communicator()->stringToIdentity("PhpRunner"));
        _adapter->activate();
        if(logLevel > 0)
        {
            ostringstream os;
            os << "PhpRunner started ok";
            logger->trace("info", os.str());
        }
        status = EXIT_SUCCESS;
        return true;
    }

    virtual bool
    stop()
    {
        _runner->stop();
        return true;
    }

private:

    Ice::ObjectAdapterPtr _adapter;
    CommandRunnerIPtr _runner;
};

int
main(int argc, char* argv[])
{
    Server app;
    return app.main(argc, argv);
}