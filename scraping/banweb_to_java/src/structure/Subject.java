package structure;

public class Subject implements Comparable<Subject> {
	protected String shortName = "";
	protected String longName = "";
	
	public Subject(String shortName, String longName)
	{
		this.shortName = shortName;
		this.longName = longName;
	}
	
	/**
	 * @return The short, computer-friendly version of the subject name.
	 */
	public String getShortName()
	{
		return shortName;
	}
	
	/**
	 * @return The long, human-friendly version of the subjent name.
	 */
	public String getLongName()
	{
		return longName;
	}

	@Override
	public int compareTo(Subject o) {
		return longName.compareTo(o.longName);
	}
	
	@Override
	public String toString() {
		return longName;
	}
}
