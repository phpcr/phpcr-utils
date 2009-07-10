
package ch.liip.jcr.jack;

import java.util.Properties;
import java.io.*;
import javax.jcr.*;
import org.apache.jackrabbit.core.TransientRepository;
import ch.liip.jcr.davex.DavexClient;

/**
Import or export the entire repository.

Importing autodetects whether you have a system view or document view file.
For exporting, there are two separate commands:
 * export for system view
 * exportdocument for document view

Export and import are done on path specified in src/jcr.properties as repository-base-xpath
Import is done with behavior IMPORT_UUID_COLLISION_THROW, that is if there already is something in the repository and that happens to have the same UUID, the import will fail.
Our primary goal is to replace the complete repository contents.

The program can be controlled by a couple of parameters that are given in the form param=value.
Parameters are specified after the import / export switch and the filename
 * username, password: credentials for login
 * repository-base-xpath: path to export to file / import file to
 * workspace: workspace to export / import
 * transport: davex to connect to a server, local to start an own jackrabbit server
 * storage: davex transport only. url for davex connection
 * jackrabbit-config, jackrabbit-home: local transport only. path to jackrabbit data folder

Default values for all parameters are set in jcr.properties
*/
public class Jack {
    public static void main(String[] args) throws Throwable {
        if (args.length < 2) {
            System.out.println("usage: java -jar jack.jar (import|export|exportdocument) file.xml");
            System.exit(2);
        }
        Jack j = new Jack(args);

        int errorState = 0;
        try {
            if ("import".equals(args[0])) {
                j.doImport(args[1]);
            } else if ("export".equals(args[0])) {
                j.doExport(args[1]);
            } else if ("exportdocument".equals(args[0])) {
                j.doExportDocument(args[1]);
            } else {
                System.out.println("Unrecognized command "+args[0]);
            }
        } catch(Throwable t) {
            System.err.println("Operation failed:");
            System.err.println(t.getMessage());
            errorState = 1;
        } finally {
            j.close();
        }
        System.exit(errorState);
    }

    //protected DavexClient client;
    protected Repository repository;
    protected Session session;
    protected Properties config;
    public Jack(String[] args) throws Throwable {
        InputStream is = getClass().getClassLoader().getResourceAsStream("jcr.properties");
        if (is==null) throw new Exception("Could not find jcr.properties inside the jar");
        config = new Properties();
        config.load(is);
        is.close();
        for (int i=2; i<args.length; i++) {
            String pv[] = args[i].split("=",2);
            if (pv.length != 2) throw new Exception("Invalid parameter "+args[i]);
            config.setProperty(pv[0], pv[1]);
        }

        String t = config.getProperty("transport");
        if ("local".equals(t)) {
            repository = new TransientRepository(config.getProperty("jackrabbit-config"),
                                                    config.getProperty("jackrabbit-home"));
        } else if ("davex".equals(t)) {
            //network access (fails because of obscure logging library incompatibilites)
            DavexClient client = new DavexClient(config.getProperty("storage"));
            repository = client.getRepository();
        } else {
            throw new Exception("Unknown transport requested: "+config.getProperty("transport"));
        }

        SimpleCredentials cred = new SimpleCredentials(config.getProperty("username"),
                                                       config.getProperty("password").toCharArray());
        session = repository.login(cred, config.getProperty("workspace"));
    }
    public void close() {
        session.logout();
    }
    protected void finalize() {
        this.close();
    }

    public void doExport(String filepath) throws Exception {
        File f = new File(filepath);
        if (f.exists()) {
            throw new IllegalArgumentException("Export file "+filepath+" is existing, can not export");
        }
        try {
            FileOutputStream os = new FileOutputStream(f);
            //export all including binary, recursive
            session.exportSystemView(config.getProperty("repository-base-xpath","/"), os, false, false);
            os.close();
        } catch(Throwable t) {
            throw new Exception("Failed to export repository at " +
                config.getProperty("repository-base-xpath","/") + " to file "+filepath+"\n"+t.toString());
        }
        System.out.println("Exported the repository to "+f);
    }

    public void doExportDocument(String filepath) throws Exception {
        File f = new File(filepath);
        if (f.exists()) {
            throw new IllegalArgumentException("Export file "+filepath+" is existing, can not export");
        }
        try {
            FileOutputStream os = new FileOutputStream(f);
            //export all including binary, recursive
            session.exportDocumentView(config.getProperty("repository-base-xpath","/"), os, false, false);
            os.close();
        } catch(Throwable t) {
            throw new Exception("Failed to export repository at " +
                config.getProperty("repository-base-xpath","/") + " to file "+filepath+"\n"+t.toString());
        }
        System.out.println("Exported the repository to "+f);
    }

    public void doImport(String filepath) throws Exception {
        File f = new File(filepath);
        if (! f.exists()) {
            throw new IllegalArgumentException("File "+filepath+" not existing, can not import");
        }
        try {
            //Clear repository first
            Node rootNode = session.getRootNode();
            NodeIterator nodeList = rootNode.getNodes();
            while (nodeList.hasNext()) {
                Node node = nodeList.nextNode();
                if (!node.getName().equals("jcr:system")) {
                    node.remove();
                    session.save();
                }
            }
            FileInputStream data = new FileInputStream(f);
            session.importXML(config.getProperty("repository-base-xpath","/"), data,
                              ImportUUIDBehavior.IMPORT_UUID_COLLISION_REPLACE_EXISTING);
        } catch(Throwable t) {
            throw new Exception("Failed to import repository to "+
                config.getProperty("repository-base-xpath","/") +
                " from file "+filepath+"\n"+t.toString());
        }
        try {
            session.save();
        } catch(Throwable t) {
            throw new Exception("Failed to save the imported repository: "+t.toString());
        }
        System.out.println("Imported the repository from "+f);
    }
}