package system.io;

import java.io.IOException;

/**
 * Used to interact with the system, whether that is files, a database, etc...
 * <p>
 * Must define an argument-less constructor or define no constructors.
 */
public interface SystemInterface {
	/**
	 * Instantialize with a handle to this interface's preferred communication
	 * channel with the given tableName. Use isWritable to open a writable
	 * communication channel.
	 * 
	 * @param tableName
	 *            The name of the table/file/etc to open with.
	 * @param isWritable
	 *            If true, will open/create an existing/new file to write to
	 *            with the given fileName. Otherwise, will attempt to read a
	 *            file.
	 * @throws IOException
	 *             If opening the file failed.
	 */
	public void initialize(String tableName, boolean isWritable) throws IOException;
}
