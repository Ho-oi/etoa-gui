
#ifndef __CONFIGHANDLER__
#define __CONFIGHANDLER__

#include <mysql++/mysql++.h>
#include <map>
#include <vector>
#include "../MysqlHandler.h"

/**
* Config Singleton, very usefull!!!!! So use it .D
* 
* \author Stephan Vock <glaubinix@etoa.ch>
*/

	class Config
	{
	public:
		static Config& instance () {
			static Config _instance;
			return _instance;
		}
		~Config () {};
		std::string get(std::string name, int value);
		double nget(std::string name, int value);
	private:
		std::map<std::string, int> sConfig;
		std::vector<std::vector<std::string> > cConfig;
		
		void loadConfig ()
		{
			My &my = My::instance();
			mysqlpp::Connection *con = my.get();
			int counter = 0;
			mysqlpp::Query query = con->query();
			query << "SELECT ";
			query << "	config_name, ";
			query << "	config_value, ";
			query << "	config_param1, ";
			query << "	config_param2 ";
			query << "FROM ";
			query << "	config;";
			mysqlpp::Result res = query.store();	
			query.reset();
		
			if (res) {
				int resSize = res.size();
				if (resSize>0) {
					mysqlpp::Row row;
					cConfig.reserve(resSize);
					for (mysqlpp::Row::size_type i = 0; i<resSize; i++)  {
						row = res.at(i);
						sConfig[std::string(row["config_name"]) ] =  (int)i;
						std::vector<std::string> temp (3);
						temp[1]=std::string(row["config_param1"]);
						temp[2]=std::string(row["config_param2"]);
						temp[0]=std::string(row["config_value"]);
						cConfig.push_back(temp);
						counter = (int)i;
					}
				}
			}
			
			query << "SELECT ";
			query << " id ";
			query << "FROM ";
			query << "	entities ";
			query << "WHERE ";
			query << "	code='m';";
			mysqlpp::Result mRes = query.store();
			query.reset();

			if (mRes) {
				int mSize = mRes.size();

				if (mSize > 0) {
					mysqlpp::Row mRow = mRes.at(0);
					sConfig.insert ( std::pair< std::string, int > ("market_entity", counter));
					std::vector<std::string> temp (3);
					temp[1]=std::string("0");
					temp[2]=std::string("0");
					temp[0]=std::string(mRow["id"]);
					cConfig.push_back(temp);
					counter++;
				}
			}
		};
		static Config* _instance;
		Config () {
			loadConfig();
		 };
		Config ( const Config& );
		Config & operator = (const Config &);
	};


#endif
