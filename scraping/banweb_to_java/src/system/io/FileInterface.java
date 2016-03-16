package system.io;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

/**
 * For interfacing with system files. This class is, in part, meant to abstract
 * away the file system and so also the file paths.
 */
public class FileInterface implements SystemInterface {
	BufferedReader readHandle = null;
	BufferedWriter writeHandle = null;

	/**
	 * {@inheritDoc}
	 * <p>
	 * Will create a new file to write to if isWritable and a file doesn't
	 * already exist.
	 * <p>
	 * Pass the fileName in for the tableName. Do not include the file path in
	 * the tableName.
	 */
	@Override
	public void initialize(String tableName, boolean isWritable) throws IOException {
		File file = new File(getPath() + tableName);
		if (isWritable) {
			writeHandle = new BufferedWriter(new FileWriter(file));
		} else {
			readHandle = new BufferedReader(new FileReader(file));
		}
	}

	/**
	 * @return The standard path to all scraped files.
	 */
	protected String getPath() {
		return System.getProperty("user.dir");
	}
}
